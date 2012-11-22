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
 * @package    Bigace_Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class can convert XML to SQL.
 *
 * The XML must have a root node with the name
 * Bigace_Db_XmlToSql_Table::XML_ROOT.
 *
 * It is mainly used for installation and managing updates and extensions.
 *
 * @category   Bigace
 * @package    Bigace_Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Db_XmlToSql_Table
{
    const XML_ROOT   = 'bigace';

    /**
     * Read the given XML file and return an array structure that
     * can be used to read and create tables (currently MySQL specific).
     *
     * Each tablename gets the $prefix prepended.
     *
     * The returned array holds arrays with the following keys:
     *
     * - name
     * - original
     * - description
     * - todo
     * - create
     *
     * @param string $filename
     * @param string $prefix
     * @return array(array(string=>string))
     */
    public function getParsedSchema($filename, $prefix = '', $type = 'mysql')
    {
        $xml  = simplexml_load_file($filename);

        if (!$this->isValidStructure($xml)) {
            throw new Bigace_Exception('Could not validate DDL XML Structure');
        }

		$columnDef = $type;

		switch($type)
		{
			case 'PDO_Sqlite':
				$columnDef = 'sqlite';
				$create = "CREATE TABLE IF NOT EXISTS `".$prefix."%s` (" .
						  PHP_EOL .
						  "%s" .
						  PHP_EOL .
						  ");";
				break;

			case 'mysql':
			case 'Mysqli':
			case 'PDO_Mysql':
			default:
				$columnDef = 'mysql';
				$create = "CREATE TABLE IF NOT EXISTS `".$prefix."%s` (" .
						  PHP_EOL .
						  "%s" .
						  PHP_EOL .
						  ") ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
				break;
		}


        foreach ($xml->table as $table) {
            $name        = (string)$table['name'];
            $description = (isset($table->description) ? $table->description : '');
            $todo        = (isset($table->todo) ? $table->todo : '');
			$columns     = (string)$table->create->$columnDef;

            $all[] = array(
                'name'        => $prefix.$name,
                'original'    => $name,
                'description' => (string)$description,
                'todo'        => (string)$todo,
                'create'      => sprintf($create, $name, $columns)
            );
        }

        return $all;
    }

    /**
     * Implement a XML check to make sure only valid XML is used.
     *
     * @return boolean
     */
    protected function isValidStructure($xml)
    {
        return true;
    }
}