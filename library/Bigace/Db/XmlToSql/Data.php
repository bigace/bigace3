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
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class provides methods for easy parsing XML Files to SQL Statements.
 *
 * The function executeSqlArray() can be used to execute the extracted
 * statements, according to the internal rules.
 *
 * REMEMBER: The <TAG function="true"> attribute is not allowed on key columns.
 *
 * Setting $parser->setMode(Bigace_Db_XmlToSql_Data::MODE_INSTALL) will result
 * in INSERT statments only.
 *
 * @category   Bigace
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Db_XmlToSql_Data
{
    const MODE_UPDATE = 'update';
    const MODE_INSTALL = 'install';
    const DEFAULT_SCHEMA_VERSION = '1.0';

    private $rootTag = 'content';
    private $schemaVersion = '1.0';
    private $ignoreVersion = false;
    private $tablePrefix = '';
    private $obj = null;
    private $sqlArray = array();
    private $replacer = array();
    private $errors = array();
    public $mode = Bigace_Db_XmlToSql_Data::MODE_UPDATE;

    /**
     * Parses a XML File name identified by its canoncial Filename.
     */
    function parseFile($filename)
    {
        $this->parseStructure($this->get_file_contents($filename));
    }

    /**
     * Returns the content of the desired file.
     */
    function get_file_contents($file)
    {
        if (function_exists('file_get_contents')) {
            return file_get_contents($file);
        }

        $f = fopen($file, 'r');
        if (!$f) {
            return '';
        }
        $t = '';

        while ($s = fread($f, 100000)) {
            $t .= $s;
        }
        fclose($f);

        return $t;
    }

    /**
     * @access private
     */
    function &_create_parser()
    {
        // Create the parser
        $xmlParser = xml_parser_create();
        xml_set_object($xmlParser, $this);

        // Initialize the XML callback functions
        xml_set_element_handler($xmlParser, '_tag_open', '_tag_close');
        xml_set_character_data_handler($xmlParser, '_tag_cdata');

        return $xmlParser;
    }

    function getVersionRegExp()
    {
        return '/<' . $this->rootTag . '.*?( version="([^"]*)")?.*?>/';
    }

    /**
     * Sets if we try to parse the Schema File, even if Version conflict is found.
     * Returns the current setting.
     */
    function setIgnoreVersionConflict($ignore = true)
    {
        if (is_bool($ignore)) {
            $this->ignoreVersion = $ignore;
        }
        return $this->ignoreVersion;
    }

    /**
     * Sets the Prefix for each Table.
     */
    function setTablePrefix($prefix = '')
    {
        $this->tablePrefix = $prefix;
    }

    /**
     * Converts the given Tablename into a prefixed one.
     */
    function prefix($tableName)
    {
        return $this->tablePrefix . $tableName;
    }

    /**
     * Returns the prefixed Table Name.
     */
    function setReplacer($replacer)
    {
        return $this->replacer = $replacer;
    }

    /**
     * Parses an XML Structure
     */
    function parseStructure($xml)
    {
        $version = $this->getSchemaVersion($xml);
        if ($version === false) {
            if ($this->ignoreVersion) {
                $this->addError('Ignoring missing schema version ... fix your XML!');
            } else {
                $this->addError('Missing schema version, skip XML parsing ... fix your XML!');
                return false;
            }
        } else if ($version != $this->schemaVersion) {
            if ($this->ignoreVersion) {
                $this->addError(
                    'Ignoring invalid schema version: ' . $version .
                    '. Current version: ' . $this->schemaVersion
                );
            } else {
                $this->addError(
                    'Skip XML parsing, invalid schema version: ' . $version .
                    '. Current version: ' . $this->schemaVersion
                );
                return false;
            }
        }

        $xmlParser = $this->_create_parser();

        // Process the XML
        if (!xml_parse($xmlParser, $xml, true)) {
            $this->addError(
                'XML Problems while parsing the Import File. ' .
                sprintf(
                    "XML error: %s at line %d.",
                    xml_error_string(xml_get_error_code($xmlParser)),
                    xml_get_current_line_number($xmlParser)
                ) .
                "\n" . $xml
            );
        }
        xml_parser_free($xmlParser);
    }

    /**
     * Returns the Array with the parsed SQL Statements.
     */
    function getSqlArray()
    {
        return $this->sqlArray;
    }

    /**
     * Sets the runtime mode for this XML Parsing.
     */
    function setMode($mode)
    {
        if (is_string($mode))
            $this->mode = $mode;
    }

    /**
     * Returns the Schema Version or FALSE.
     */
    function getSchemaVersion($xmlstring)
    {
        if (!is_string($xmlstring) OR empty($xmlstring)) {
            return FALSE;
        }

        if (preg_match($this->getVersionRegExp(), $xmlstring, $matches)) {
            return (!empty($matches[2]) ? $matches[2] : Bigace_Db_XmlToSql_Data::DEFAULT_SCHEMA_VERSION);
        }

        return FALSE;
    }

    /**
     * Executes an array via statements which failed
     */
    function executeSqlArray(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $errors = array();

        foreach ($this->sqlArray as $row) {
            $needsUpdate = false;
            $wantUpdate = (isset($row['update']) && is_array($row['update'])
                && count($row['update']) > 0);

            if (isset($row['select'])) {
                try {
                    $select = $dbAdapter->select()
                            ->from($row['table'])
                            ->where($row['select']);
                    $temp = $dbAdapter->query($select);
                } catch (Exception $e) {
                    $errors[] = 'Failed to select: "' . $select->assemble();
                    //$errors[] = 'Failed to select "' . $row['select'] . '" from "'
                    //    . $row['table'] . '":' . $e->getMessage();
                    continue;
                }

                if ($temp && $temp->rowCount() > 0) {
                    // @kpapst replaced for 3.0: the installer should not "update"
                    // existing communities on failure - which will only happen if
                    // you submit an existing Community ID via setReplacer()
                    // if($this->mode != Bigace_Db_XmlToSql_Data::MODE_INSTALL)
                    // previously only the following check was used
                    // if(is_string($update))
                    // for now, lets not use any mode - it just makes troubles
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate === false) {
                try {
                    $res = $dbAdapter->insert($row['table'], $row['insert']);
                    if ($res === false || $res == 0) {
                        $errors[] = 'Failed to insert into "' . $row['table'] . '"' .
                            ' values => ' . print_r($row['insert'], true);
                    }
                } catch (Exception $e) {
                    $errors[] = '(2) Failed to insert into "' . $row['table'] . '"' .
                        ' values => ' . print_r($row['insert'], true) .
                        ' because of: ' . $e->getMessage();
                }
            } else {
                if ($wantUpdate) {
                    try {
                        $res = $dbAdapter->update($row['table'], $row['update']['values'], $row['update']['where']);
                        // do not check for amount here, it might be 0 when the row didn't need to be upgraded
                        if ($res === false) {
                            $errors[] = 'Failed to update "' . $row['table'] . '" where "' .
                                $row['update']['where'] . '" with => ' . print_r($row['update']['values'], true);
                        }
                    } catch (Exception $e) {
                        $errors[] = '(2) Failed to update "' . $row['table'] . '" where "' .
                            $row['update']['where'] . '" with => ' . print_r($row['update']['values'], true) .
                            ' because of: ' . $e->getMessage();
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * @access private
     */
    function addSQL($values, $mode = null)
    {
        if ($mode == null) {
            $mode = Bigace_Db_XmlToSql_Data::MODE_UPDATE;
        }

        if ($mode == $this->mode) {
            if (isset($values['select'])) {
                foreach ($this->replacer as $search => $replace) {
                    $values['select'] = str_replace($search, $replace, $values['select']);
                }
            }

            if (isset($values['update']['where'])) {
                foreach ($this->replacer as $search => $replace) {
                    $values['update']['where'] = str_replace($search, $replace, $values['update']['where']);
                }
            }

            if (isset($values['update']['values'])) {
                foreach ($values['update']['values'] as $key => $value) {
                    if (is_string($value)) {
                        foreach ($this->replacer as $search => $replace) {
                            $values['update']['values'][$key] = str_replace($search, $replace, $value);
                        }
                    }
                }
            }

            if (isset($values['insert'])) {
                foreach ($values['insert'] as $key => $value) {
                    if (is_string($value)) {
                        foreach ($this->replacer as $search => $replace) {
                            $values['insert'][$key] = str_replace($search, $replace, $value);
                        }
                    }
                }
            }

            $this->sqlArray[] = $values;
        }
        return true;
    }

    /**
     * @access private
     */
    function addError($msg)
    {
        $this->errors[] = $msg;
    }

    function getError()
    {
        return $this->errors;
    }

    // ---------------------------------------------------------------------------------

    /**
     * XML Callback to process start elements
     *
     * @access private
     */
    function _tag_open(&$parser, $tag, $attributes)
    {
        switch (strtoupper($tag)) {
            case 'TABLE':
                $this->obj = new XmlDbTable($this, $attributes);
                xml_set_object($parser, $this->obj);
                break;
            case 'CONTENT':
                break;
            default:
                $this->addError('Wrong Field in _tag_open in XmlToSQql: ' . $tag);
                break;
        }
    }

    /**
     * XML Callback to process CDATA elements
     *
     * @access private
     */
    function _tag_cdata(&$parser, $cdata)
    {

    }

    /**
     * XML Callback to process end elements
     *
     * @access private
     * @internal
     */
    function _tag_close(&$parser, $tag)
    {

    }

}

/**
 * Abstract DB Object. This class provides basic methods for database objects, such
 * as tables and indexes.
 * @access private
 */
class XmlDbObject
{

    /**
     * var object Parent
     */
    var $parent;
    /**
     * var string current element
     */
    var $currentElement;

    /**
     * NOP
     */
    function XmlDbObject(&$parent, $attributes = NULL)
    {
        $this->parent = & $parent;
    }

    /**
     * XML Callback to process start elements
     *
     * @access private
     */
    function _tag_open(&$parser, $tag, $attributes)
    {

    }

    /**
     * XML Callback to process CDATA elements
     *
     * @access private
     */
    function _tag_cdata(&$parser, $cdata)
    {

    }

    /**
     * XML Callback to process end elements
     *
     * @access private
     */
    function _tag_close(&$parser, $tag)
    {

    }

    function create(&$parent)
    {
        return array();
    }

    /**
     * Destroys the object
     */
    function destroy()
    {
        unset($this);
    }

    /**
     * Returns the prefix set by the ranking ancestor of the database object.
     *
     * @param string $name Prefix string.
     * @return string Prefix.
     */
    function prefix($name = '')
    {
        return is_object($this->parent) ? $this->parent->prefix($name) : $name;
    }

    /**
     * Adds another SQL Statement.
     */
    function addSQL($values, $mode = null)
    {
        $this->parent->addSQL($values, $mode);
    }

    /**
     * Escapes and Quotes the SQL Value.
     */
    function escapeAndQuoteValue($value)
    {
        return "'" . addslashes($value) . "'";
    }

}

/**
 * Creates a table object.
 *
 * This class stores information about a database table. As charactaristics
 * of the table are loaded from the external source, methods and properties
 * of this class are used to build up the table description.
 * @access private
 */
class XmlDbTable extends XmlDbObject
{

    /**
     * @var string Table name
     * @access private
     */
    var $name;
    /**
     * @var object current DbRow
     * @access private
     */
    var $row = null;
    /**
     * @var string the table mode
     * @access private
     */
    var $mode = null;
    /**
     * @var boolean table wants to be updated?
     * @access private
     */
    var $update = true;

    /**
     * Initializes a new table object.
     *
     * @param string $prefix DB Object prefix
     * @param array $attributes Array of table attributes.
     */
    function XmlDbTable(&$parent, $attributes = NULL)
    {
        $this->parent = & $parent;
        $this->name = $this->prefix($attributes['NAME']);

        if (isset($attributes['MODE'])) {
            $this->mode = $attributes['MODE'];
        } else {
            $this->mode = $parent->mode;
        }

        if (isset($attributes['UPDATE'])) {
            if (strtolower($attributes['UPDATE']) == 'false')
                $this->update = false;
            else
                $this->update = (bool) $attributes['UPDATE'];
        }
    }

    /**
     * Returns the prefixed Table Name.
     */
    function getTableName()
    {
        return $this->name;
    }

    /**
     * XML Callback to process start elements. Elements currently
     * processed are: ROW
     *
     * @access private
     */
    function _tag_open(&$parser, $tag, $attributes)
    {
        $this->currentElement = strtoupper($tag);

        switch ($this->currentElement) {
            case 'ROW':
                if ($this->mode != null && !isset($attributes['MODE']))
                    $attributes['MODE'] = $this->mode;
                if (!isset($attributes['UPDATE']))
                    $attributes['UPDATE'] = $this->update;
                $this->row = new XmlDbRow($this, $attributes);
                xml_set_object($parser, $this->row);
                break;
            default:
                $this->addError('Wrong Field in _tag_open in XmlDbTable: ' . $tag);
                break;
        }
    }

    /**
     * XML Callback to process CDATA elements
     *
     * @access private
     */
    function _tag_cdata(&$parser, $cdata)
    {

    }

    /**
     * XML Callback to process end elements
     *
     * @access private
     */
    function _tag_close(&$parser, $tag)
    {
        $this->currentElement = '';

        switch (strtoupper($tag)) {
            case 'TABLE':
                xml_set_object($parser, $this->parent);
                $this->destroy();
                break;
            default:
                $this->addError('Wrong Field in _tag_close in XmlDbTable: ' . $tag);
                break;
        }
    }

}

/**
 * Creates a table row object.
 * * This class stores information about a database row.
 * @access private
 */
class XmlDbRow extends XmlDbObject
{

    /**
     * @access private
     */
    var $update = false;
    /**
     * @access private
     */
    var $columns = array();
    /**
     * @access private
     */
    var $currentColumn = null;
    /**
     * @access private
     */
    var $rowMode;
    /**
     * @access private
     */
    var $passedColumns = array();

    /**
     * Iniitializes a new row object.
     *
     * @param string $parent the Table DB Object
     * @param array $attributes Array of table attributes.
     */
    function XmlDbRow(&$parent, $attributes = NULL)
    {
        $this->parent = & $parent;

        if (isset($attributes['UPDATE'])) {
            if (strtolower($attributes['UPDATE']) == 'false')
                $this->update = false;
            else
                $this->update = (bool) $attributes['UPDATE'];
        }

        if (isset($attributes['MODE'])) {
            $this->rowMode = $attributes['MODE'];
        } else {
            $this->rowMode = $parent->mode;
        }
    }

    /**
     * XML Callback to process start elements. Elements currently
     * processed are: ROW
     *
     * @access private
     */
    function _tag_open(&$parser, $tag, $attributes)
    {
        $this->currentElement = strtoupper($tag);
        $this->currentColumn = array('name' => $tag,
            'key' => (isset($attributes['KEY']) && $attributes['KEY'] == 'true' ? true : false),
            'func' => (isset($attributes['FUNCTION']) && $attributes['FUNCTION'] == 'true' ? true : false),
            'null' => (isset($attributes['NULL']) && $attributes['NULL'] == 'true' ? true : false),
            'value' => ''
        );
    }

    /**
     * XML Callback to process CDATA elements
     *
     * @access private
     */
    function _tag_cdata(&$parser, $cdata)
    {
        if ($this->currentElement != null && $this->currentElement != '') {
            $this->currentColumn['value'] .= $cdata;
        }
    }

    /**
     * XML Callback to process end elements
     *
     * @access private
     */
    function _tag_close(&$parser, $tag)
    {
        $this->currentElement = '';

        switch (strtoupper($tag)) {
            case 'ROW':
                $this->create($this->parent);
                xml_set_object($parser, $this->parent);
                $this->destroy();
                break;
            default:
                $this->columns[] = $this->currentColumn;
                $this->currentColumn = null;
                break;
        }
    }

    function create(&$parent)
    {
        $updateWhere = '';
        $select = '';
        $inserts = array();
        $updates = array();

        for ($i = 0; $i < count($this->columns); $i++) {
            $column = $this->columns[$i];
            $name = strtolower($column['name']);
            if ($column['func'] === false) {
                if ($column['null'] === false) {
                    $value = $column['value'];
                } else {
                    $value = null;
                }
            } else {
                $value = new Zend_Db_Expr($column['value']);
            }

            // prepare select and update statement
            if ($column['key']) {
                $v = $this->escapeAndQuoteValue($value);
                if ($select != '') {
                    $select .= ' AND ';
                }
                $select .= $name . '=' . $v;

                if ($updateWhere != '') {
                    $updateWhere .= ' AND ';
                }

                $updateWhere .= $name . '=' . $v;
            } else {
                $updates[$name] = $value;
            }

            // prepare insert statement
            if (!isset($this->passedColumns[$name])) {
                $inserts[$name] = $value;
            }
            // remember the column name
            $this->passedColumns[$name] = $name;
        }

        $values = array(
            'table' => $parent->getTableName(),
            'insert' => $inserts,
            'update' => array(
                'values' => $updates,
                'where' => $updateWhere
            ),
            'select' => $select
        );

        // column does not want to be updated!
        if (!$this->update) {
            $values['update'] = array();
        }

        $this->addSQL($values, $this->rowMode);

        return true;
    }

}