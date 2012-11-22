<?php
/**
 * Bigace - a PHP and MySQL based Web CMS.
 *
 * LICENSE
 *
 * This source file is subject to the new GNU General Public License
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.bigace.de/license.html
 *
 * Bigace is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * @category   Bigace
 * @package    Bigace_Acl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

import('classes.fright.FrightAdminService');
import('classes.fright.FrightStringsEnumeration');
import('classes.group.GroupAdminService');
import('classes.group.GroupService');

/**
 * The importer is able to recover previously exported user-groups and
 * permissions from an XML file (or string).
 *
 * Following rules apply:
 *
 * - the import does not delete existing groups
 * - if a group exists, it will be updated and all existing permission mappings will be deleted
 * - if a permission exists, it will be updated
 *
 * Please note 2: It does not handle item permissions.
 *
 * @category   Bigace
 * @package    Bigace_Acl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Acl_Importer
{

    private $xmlParser;
    private $inNode = array();
    private $currentValues = array();
    // the tag we are currently in
    private $currentTag = '';

    private $allPermissions = array();

    /**
     * Import an XML File
     */
    public function importFile($filename)
    {
        $couldRead = FALSE;
        if (file_exists($filename)) {
            $extension = IOHelper::getFileExtension($filename);
            if (strnatcasecmp($extension, 'xml') == 0) {
                $content = file_get_contents($filename);
                $couldRead = $this->importXML($content);
                if ($couldRead) {
                    $GLOBALS['LOGGER']->logInfo('Imported permissions from: ' . $filename);
                }
            } else {
                $GLOBALS['LOGGER']->logError(
                    'File to import has wrong extension ['.$extension.'], expected [xml]'
                );
            }
        } else {
            $GLOBALS['LOGGER']->logError('Could not find file to import: ' . $filename);
        }

        return $couldRead;
    }

    public function importXML($xml)
    {
        if ($this->checkForIntegrity($xml)) {
            // preload all permissions
            $all = new FrightStringsEnumeration();
            $c = $all->count();
            for ($i = 0; $i < $c; $i++) {
                $perm = $all->next();
                $this->allPermissions[] = $perm->getName();
            }

            $this->xmlParser = xml_parser_create();

            xml_set_object($this->xmlParser, $this);
            xml_parser_set_option($this->xmlParser, XML_OPTION_CASE_FOLDING, false);
            xml_set_element_handler($this->xmlParser, "tag_open", "tag_close");
            xml_set_character_data_handler($this->xmlParser, "cdata");

            // do Parsing
            if (!xml_parse($this->xmlParser, $xml, true)) {
                $GLOBALS['LOGGER']->logError(
                    'XML Problems while parsing the uploaded permissions file. ' .
                    sprintf(
                        "XML error: %s at line %d.",
                        xml_error_string(xml_get_error_code($this->xmlParser)),
                        xml_get_current_line_number($this->xmlParser)
                    )
                    . "\n" . $xml
                );
                return false;
            }

            xml_parser_free($this->xmlParser);
            return true;
        }

        return false;
    }

    /**
     * Checks if the given XML is proper formatted.
     */
    private function checkForIntegrity($xml)
    {
        try {
            $doc = new DOMDocument();
            if ($doc->loadXML($xml)) {
                if(strcmp(Bigace_Acl_Exporter::NODE_ROOT, $doc->documentElement->tagName) !== 0)
                return false;

                return true;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Get the Node we are currently parsing!
     * @access private
     */
    private function getCurrentNode()
    {
        return $this->currentTag;
    }

    /**
     * Set the Node we are currently parsing!
     */
    private function setCurrentNode($tag)
    {
        $this->currentTag = $tag;
    }

    private function setCurrentValue($value)
    {
        $value = trim($value);
        if ($value != '' && $value != "\n") {
            $this->currentValues[$this->getCurrentNode()] = $value;
        }
    }

    private function getCurrentValue()
    {
        return $this->currentValues[$this->getCurrentNode()];
    }

    /**
     * Clears all values currently cached!
     */
    private function clearCurrentValue()
    {
        return $this->currentValues = array();
    }

    private function tag_open($parser, $tag, $attributes)
    {
        $this->checkNode(Bigace_Acl_Exporter::NODE_GROUP, $tag, TRUE);
        $this->checkNode(Bigace_Acl_Exporter::NODE_FRIGHT, $tag, TRUE);
        $this->checkNode(Bigace_Acl_Exporter::NODE_MAPPING, $tag, TRUE);

        if (strnatcasecmp(Bigace_Acl_Exporter::NODE_GROUP, $tag) == 0) {
            $this->clearCurrentValue();
        } else if (strnatcasecmp(Bigace_Acl_Exporter::NODE_FRIGHT, $tag) == 0) {
            $this->clearCurrentValue();
        } else if (strnatcasecmp(Bigace_Acl_Exporter::NODE_MAPPING, $tag) == 0) {
            $this->clearCurrentValue();
        }

        $this->setCurrentNode($tag);
    }

    private function cdata($parser, $cdata)
    {
        if ($this->isInNode(Bigace_Acl_Exporter::NODE_GROUP) ||
            $this->isInNode(Bigace_Acl_Exporter::NODE_FRIGHT) ||
            $this->isInNode(Bigace_Acl_Exporter::NODE_MAPPING)) {
                $this->setCurrentValue($cdata);
        }
    }

    private function tag_close($parser, $tag)
    {
        if (strnatcasecmp(Bigace_Acl_Exporter::NODE_GROUP, $tag) == 0) {
            if (isset ($this->currentValues[Bigace_Acl_Exporter::NODE_GROUP_ID])
            && isset ($this->currentValues[Bigace_Acl_Exporter::NODE_GROUP_NAME])) {

                $id = $this->currentValues[Bigace_Acl_Exporter::NODE_GROUP_ID];
                $name = $this->currentValues[Bigace_Acl_Exporter::NODE_GROUP_NAME];

                $groupService = new GroupService();
                $group = $groupService->getGroup($id);

                $groupAdmin = new GroupAdminService();
                if ($group === null) {
                    $groupAdmin->createGroupWithID($id, $name);
                } else {
                    $groupAdmin->updateGroup($id, $name);
                    // this group already exists, so we are going to drop all
                    // permissions to make sure only imported values apply
                    $frightAdmin = new FrightAdminService();
                    $frightAdmin->deleteAllGroupFrights($id);
                }
            }

        } else if (strnatcasecmp(Bigace_Acl_Exporter::NODE_FRIGHT, $tag) == 0) {

            if (isset ($this->currentValues[Bigace_Acl_Exporter::NODE_FRIGHT_NAME]) &&
                isset ($this->currentValues[Bigace_Acl_Exporter::NODE_FRIGHT_DESCRIPTION])) {

                $name = $this->currentValues[Bigace_Acl_Exporter::NODE_FRIGHT_NAME];
                $desc = $this->currentValues[Bigace_Acl_Exporter::NODE_FRIGHT_DESCRIPTION];

                $frightAdmin = new FrightAdminService();
                if (!in_array($name, $this->allPermissions)) {
                    $frightAdmin->createFright($name, $desc);
                } else {
                    $frightAdmin->changeFright($name, $desc);
                }
            }

        } else if (strnatcasecmp(Bigace_Acl_Exporter::NODE_MAPPING, $tag) == 0) {
            if (isset ($this->currentValues[Bigace_Acl_Exporter::NODE_GROUP_ID]) &&
                isset ($this->currentValues[Bigace_Acl_Exporter::NODE_FRIGHT_NAME])) {

                $gid = $this->currentValues[Bigace_Acl_Exporter::NODE_GROUP_ID];
                $fname = $this->currentValues[Bigace_Acl_Exporter::NODE_FRIGHT_NAME];

                $frightAdmin = new FrightAdminService();
                $frightAdmin->createGroupFright($gid, $fname);
            }
        }

        $this->checkNode(Bigace_Acl_Exporter::NODE_GROUP, $tag, FALSE);
        $this->checkNode(Bigace_Acl_Exporter::NODE_FRIGHT, $tag, FALSE);
        $this->checkNode(Bigace_Acl_Exporter::NODE_MAPPING, $tag, FALSE);
    }

    private function checkNode($name, $tag, $value)
    {
        if (strnatcasecmp($name, $tag) == 0) {
            $this->setIsInNode($name, $value);
        }
    }

    private function setIsInNode($name, $value)
    {
        $this->inNode[$name] = $value;
    }

    private function isInNode($name)
    {
        if (!isset($this->inNode[$name])) {
            return FALSE;
        } else {
            return $this->inNode[$name];
        }
    }
}
