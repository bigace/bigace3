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
 * @package    Bigace_Content
 * @subpackage Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A "binary" content object uses files to handle content entries.
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @subpackage Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Content_Item_Binary extends Bigace_Content_Item_Text
{

    private $filename;

    /**
     * Sets the filename.
     *
     * @param string $filename
     * @return Bigace_Content_Item_Binary
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Returns the filename for this binary file.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the content itself if set, otherwise the content denoted by filename.
     * @return String
     */
	public function getContent()
	{
	    if ($this->content !== null) {
            return $this->content;
	    }

        $content = '';
        $size    = filesize($this->filename);
        if ($size > 0) {
            $fp      = fopen($this->filename, "rb");
            $content = fread($fp, $size);
            fclose($fp);
        }
        return $content;
	}

    /**
     * Returns the filesize of this content item.
     * The filesize is just approximately correct and therefor might differ from the real value!
     *
     * @return integer
     */
    public function getSize()
    {
        if ($this->content !== null) {
            return parent::getSize();
        }

        return filesize($this->filename);
    }


}