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

/**
 * The exporter creates a XML export of your user-groups, permissions and
 * the group-permission mappings.
 *
 * This export can be imported by the Bigace_Acl_Importer.
 *
 * Please note: It does NOT handle item permissions.
 *
 * @category   Bigace
 * @package    Bigace_Acl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Acl_Exporter
{

    const NODE_ROOT = 'Bigace_User_Permissions';
    const NODE_GROUP_ID = 'id';
    const NODE_MAPPING = 'user-group';
    const NODE_GROUP = 'group';
    const NODE_FRIGHT = 'permission';
    const NODE_GROUP_NAME = 'name';
    const NODE_FRIGHT_NAME = 'name';
    const NODE_FRIGHT_DESCRIPTION = 'description';
    const NODE_GROUPS = 'GROUPS';
    const NODE_FRIGHTS = 'PERMISSIONS';
    const NODE_MAPPINGS = 'MAPPINGS';

    private $export = '';

    public function __construct()
    {
        import('classes.group.GroupService');
        import('classes.util.IOHelper');
    }

    function getExportArray()
    {
        $xmlExport = array(
            self::NODE_GROUPS => array(),
            self::NODE_FRIGHTS => array(),
            self::NODE_MAPPINGS => array()
        );

        // export the frights itself with name, default-value and description
        $enum = new FrightStringsEnumeration();
        for ($i=0; $i < $enum->count(); $i++) {
            $temp = $enum->next();
            array_push(
                $xmlExport[self::NODE_FRIGHTS],
                array(
                    self::NODE_FRIGHT => array(
                        self::NODE_FRIGHT_NAME => $temp->getName(),
                        self::NODE_FRIGHT_DESCRIPTION => $temp->getDescription()
                    )
                )
            );
        }

        // export the groups with id and name
        $groupService = new GroupService();
        $allGroups = $groupService->getAllGroups();
        foreach ($allGroups as $temp) {
            array_push(
                $xmlExport[self::NODE_GROUPS],
                array(
                    self::NODE_GROUP => array(
                        self::NODE_GROUP_ID => $temp->getID(),
                        self::NODE_GROUP_NAME => $temp->getName()
                    )
                )
            );
        }

        // export all fright-mappings for the groups
        $allGroups = $groupService->getAllGroups();
        foreach ($allGroups as $temp) {
            $groupRights = new GroupFrightEnumeration($temp->getID());
            for ($a=0; $a < $groupRights->count(); $a++) {
                $tempRight = $groupRights->next();
                array_push(
                    $xmlExport[self::NODE_MAPPINGS], array(
                        self::NODE_MAPPING => array(
                            self::NODE_GROUP_ID => $temp->getID(),
                            self::NODE_FRIGHT_NAME => $tempRight->getID()
                        )
                    )
                );
            }
        }
        return $xmlExport;
    }

    public function saveDump($filename = '', $dirname = '')
    {
        if ($filename == '') {
            $filename = 'fright_export_' . time() . '.' . _IMPORT_FILE_EXTENSION;
        }

        if ($dirname == '') {
            if (!Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
                throw new Bigace_Exception('Could neither determine Community nor export folder.');
            }
            /* @var $community Bigace_Community */
            $community = Zend_Registry::get('BIGACE_COMMUNITY');
            $dirname = $community->getPath('modules') . 'export/';
        }

        IOHelper::createDirectory($dirname);

        $fullfile = $dirname . $filename;

        $fpointer = fopen($fullfile, "wb");
        fputs($fpointer, $this->getDump());
        fclose($fpointer);

        return $fullfile;
    }

    public function getDump()
    {
        $toXml = $this->getExportArray();
        $this->createDump($toXml);
        return $this->export;
    }

    private function createDump($toXml)
    {
        $this->showStartTag('?xml version="1.0" encoding="utf-8"?', 0, TRUE);

        $startEnd = self::NODE_ROOT;

        $this->showStartTag($startEnd, 0, TRUE);
        if (isset($toXml[self::NODE_GROUPS])) {
            $this->showTree(self::NODE_GROUPS, $toXml[self::NODE_GROUPS]);
        }

        if (isset($toXml[self::NODE_FRIGHTS])) {
            $this->showTree(self::NODE_FRIGHTS, $toXml[self::NODE_FRIGHTS]);
        }

        if (isset($toXml[self::NODE_MAPPINGS])) {
            $this->showTree(self::NODE_MAPPINGS, $toXml[self::NODE_MAPPINGS]);
        }
        $this->showEndTag($startEnd, 0, TRUE, TRUE);
    }

    private function showTree($arrayKey, $values)
    {
        $this->showStartTag($arrayKey, 1);
        foreach ($values as $key => $entry) {
            $this->showXml($entry, 2);
        }
        $this->showEndTag($arrayKey, 1, TRUE, TRUE);
    }

    private function showStartTag($name, $depth, $addBr = TRUE)
    {
        $this->showDepth($depth);
        $this->showElement($name, $depth, $addBr);
    }

    private function showEndTag($name, $depth, $addBr = TRUE, $showDepth = FALSE)
    {
        if ($showDepth)
        $this->showDepth($depth);
        $this->showElement('/'.$name, $depth, $addBr);
    }

    private function showElement($name, $depth, $addBr)
    {
        $this->addToString('<'.$name.'>');
        if ($addBr) {
            $this->addToString("\n");
        }
    }

    private function showDepth($depth)
    {
        for ($i=0; $i < $depth; $i++) {
            $this->addToString('   ');
        }
    }

    private function showXml($toXml, $depth)
    {
        foreach ($toXml as $name => $value) {
            if (is_array($value)) {
                $this->showStartTag($name, $depth, TRUE);
                $this->showXml($value, $depth+1);
                $this->showEndTag($name, $depth, TRUE, TRUE);
            } else {
                $this->showStartTag($name, $depth, FALSE);
                $this->addToString($value);
                $this->showEndTag($name, $depth, TRUE, FALSE);
            }
        }
    }

    private function addToString($s)
    {
        $this->export .= $s;
    }

}
