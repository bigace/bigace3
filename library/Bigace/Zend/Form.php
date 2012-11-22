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
 * @package    Bigace_Zend
 * @subpackage Form
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Basic Bigace form.
 *
 * Automatically loads Zend_Validate message in the current session language.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Form
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Form extends Zend_Form
{
    protected $language = null;

    /**
     * Constructor
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->addPrefixPath(
            'Bigace_Zend_Form_Element_', BIGACE_LIBS.'Zend/Form/Element/', 'element'
        );
        $this->addPrefixPath(
            'Bigace_Zend_Form_Decorator_', BIGACE_LIBS.'Zend/Form/Decorator/', 'decorator'
        );

        $lang = null;
        if ($options === null) {
            /* @var $session Bigace_Session */
            $session = Zend_Registry::get('BIGACE_SESSION');
            if ($session !== null) {
                $lang = $session->getLanguageID();
            }
        } else if ($options instanceof Zend_Config && isset($options->language)) {
            $lang = $options->language;
        } else if (is_array($options) && isset($options['language'])) {
            $lang = $options['language'];
        }

        if ($lang === null) {
            $lang = _ULC_;
        }
        $this->setLanguage($lang);
        parent::__construct($options);
    }

    /**
     * Adds a translation by merghing it with the existing one.
     * This is convenience behaviour, to keep all validation error messages.
     *
     * @param string $name
     */
    public function addTranslation($name)
    {
        $file = Bigace_Translate::get($name, $this->language);
        $this->getTranslator()->addTranslation($file);
    }

    /**
     * Loads the default Zend_Validator messages in the given $language.
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        if (file_exists(BIGACE_I18N . $language . '/Zend_Validate.php')) {
            $this->language = $language;

            $translator = new Zend_Translate(
                array(
                    'adapter' => 'array',
                    'content' => BIGACE_I18N . $language . '/Zend_Validate.php',
                    'locale'  => $language
                )
            );
            Zend_Validate_Abstract::setDefaultTranslator($translator);
            Zend_Form::setDefaultTranslator($translator);
        }
    }

    /**
     * Returns a configured Captcha Element.
     *
     * @param string $name
     * @param string $label
     * @return Zend_Form_Element_Captcha
     */
    protected function createCaptcha($name = 'captcha', $label = 'Catpcha')
    {
        if (!Bigace_Session::isStarted()) {
            /* @var $session Bigace_Session */
            $session = Zend_Registry::get('BIGACE_SESSION');
            $session->start();
        }

        $element = new Zend_Form_Element_Captcha($name, array(
            'label'   => $label,
            'captcha' => 'Figlet',
            'class'   => 'captcha',
            'captchaOptions' => array(
                'wordLen' => 4,
                'timeout' => 600,
            ),
            'required' => true
        ));
        $captcha = Bigace_Services::get()->getService(Bigace_Services::CAPTCHA);
        $element->setCaptcha($captcha);
        return $element;
    }

}