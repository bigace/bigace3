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
 * @package    Bigace_View
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Using Zend_Layout and Zend_View to render pages.
 *
 * @category   Bigace
 * @package    Bigace_View
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_View_Engine_Zend implements Bigace_View_Engine
{
    /**
     * Contains the realpath where the layouts are stored.
     *
     * @var string
     */
    private $layoutFolder = null;

    /**
     * Returns the folder where layouts are stored.
     *
     * @see Bigace_View_Engine::getTemplateFolder()
     * @return string
     */
    protected function getTemplateFolder()
    {
        if ($this->layoutFolder === null) {
            if (!Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
                throw new Bigace_View_Exception('Community was null, cannot detect layout folder.');
            }
            $community = Zend_Registry::get('BIGACE_COMMUNITY');
            $this->layoutFolder = $community->getPath('layout');
        }
        return $this->layoutFolder;
    }

    /**
     * Returns the Controller name that is used to render templates/layouts.
     *
     * @see Bigace_View_Engine::getControllerName()
     * @return string the controller name in lower case
     */
    public function getControllerName()
    {
        return 'page';
    }

    /**
     * @see Bigace_View_Engine::getLayouts()
     * @return array(Bigace_View_Layout_Zend)
     */
    public function getLayouts()
    {
        $res = array();

        $folder = $this->getTemplateFolder();
        foreach (glob($folder.'*.phtml') as $ff) {
            $name = str_replace('.phtml', '', str_replace($folder, '', $ff));
            $res[] = $this->getLayout($name);
        }

        return $res;
    }

    /**
     * @see Bigace_View_Engine::getLayout()
     * @param string $name
     * @return Bigace_View_Layout_Zend|null
     */
    public function getLayout($name = '')
    {
        if ($name == '') {
            $name = Bigace_Config::get('templates', 'default', 'default');
        }

        $folder = $this->getTemplateFolder();
        $file   = $folder.$name.'.phtml';

        if (!file_exists($file)) {
            return null;
        }

        return new Bigace_View_Layout_Zend($name);
    }

    /**
     * @see Bigace_View_Engine::startMvc()
     */
    public function startMvc($layout)
    {
        // path also used in Bigace_View_Layout_Zend
        $layoutPath = $this->getTemplateFolder();

        Zend_Layout::startMvc(
            array(
                'layout'     => $layout,
                'layoutPath' => $layoutPath
            )
        );
    }

    /**
     * @see Bigace_View_Engine::create()
     */
    public function create($name, $content = null)
    {
        if ($name === null || strlen(trim($name)) <= 1) {
            throw new Bigace_View_Exception('Layout name must at least consist of 2 character.');
        }

        $folder = $this->getTemplateFolder();
        $file   = $folder.trim($name).'.phtml';

        if (file_exists($file)) {
            throw new Bigace_View_Exception('Layout ' . $name . ' already exists.');
        }

        import('classes.util.IOHelper');
        IOHelper::writeFileContent($file, $content);
    }

    /**
     * @see Bigace_View_Engine::getSource()
     */
    public function getSource(Bigace_View_Layout $layout)
    {
        $folder = $this->getTemplateFolder();
        $file   = $folder.$layout->getName().'.phtml';

        if (!file_exists($file)) {
            throw new Bigace_View_Exception('Layout ' . $layout->getName() . ' does not exist.');
        }

        import('classes.util.IOHelper');
        return IOHelper::getFileContent($file);
    }

    /**
     * @see Bigace_View_Engine::save()
     */
    public function save(Bigace_View_Layout $layout, $content)
    {
        $folder = $this->getTemplateFolder();
        $file   = $folder.$layout->getName().'.phtml';

        import('classes.util.IOHelper');
        IOHelper::writeFileContent($file, $content);
    }

}