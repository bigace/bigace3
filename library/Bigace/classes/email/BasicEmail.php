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
 * @package    bigace.classes
 * @subpackage email
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Holds all needed System email settings.
 */

/**
 * Class used for creating an Email.
 *
 * Uses the configured email settings, if you do not supply different settings.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage email
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class BasicEmail
{
    const TRANSPORT_MAIL = 'mail';
    const TRANSPORT_SMTP = 'smtp';
    const TRANSPORT_SENDMAIL = 'sendmail';
    const TRANSPORT_QMAIL = 'qmail';

    const TYPE_PLAIN = 'plain';
    const TYPE_HTML = 'html';
    const TYPE_BOTH = 'both';

    private $mime;                  // mime type		- deprecated
    private $charset;               // email charset		- deprecated ?
    private $reply;                 // reply adress             - deprecated ?
    private $errors;                // error address		- deprecated ?
    private $encoding;              // mail encoding		- deprecated ?

    private $contentHtml;          // html content
    private $contentPlain;         // plain content
    private $to = '';               // recipient adress
    private $toName = '';           // recipient name
    private $fromName;              // send email from name
    private $fromEmail;             // send email from address
    private $server;                // smtp server
    private $subject;               // mail subject

    private $type = 'plain';        // the email type to send
    private $method = 'mail';       // the method how to send an email
    private $error = "";            // error string, if send failed!

    public function __construct($type = null)
    {
        if (!class_exists('PHPMailer')) {
            require_once(BIGACE_3RDPARTY.'phpmailer/class.phpmailer.php');
        }

        if (!class_exists('PHPMailer')) {
            throw new Bigace_Zend_Exception('PHPMailer is not installed.');
        }

        $this->resetSettings();
        if ($type !== null) {
            $this->setContentType($type);
        }
    }

    public function resetSettings()
    {
        $this->type      = Bigace_Config::get("email", "content.type");
        $this->server    = Bigace_Config::get("email", "smtp.server");
        $this->charset   = Bigace_Config::get("email", "character.set");
        $this->fromName  = Bigace_Config::get("email", "from.name");
        $this->fromEmail = Bigace_Config::get("email", "from.address");
        $this->encoding  = Bigace_Config::get("email", "encoding");

        $this->to = '';
        $this->contentPlain = '';
        $this->contentHtml = '';
        $this->reply = '';
        $this->subject = '';
        $this->errors = '';
    }

    public function setContentEncoding($val)
    {
        $this->encoding = $val;
    }

    public function setErrorsTo($val)
    {
        $this->errors = $val;
    }

    public function setCharSet($val)
    {
        $this->charset = $val;
    }

    public function setCharacterSet($val)
    {
        $this->setCharSet($val);
    }

    public function setReplyTo($val)
    {
        $this->reply = $val;
    }

    /**
     * Sets the SMTP server to use.
     * @param string $val
     */
    function setServer($val)
    {
        $this->server = $val;
    }

    /**
     * Sets the content type to send.
     * @see BasicEmail::TYPE_PLAIN (default)
     * @see BasicEmail::TYPE_BOTH
     * @see BasicEmail::TYPE_HTML
     */
    public function setContentType($val)
    {
        $this->type = $val;
    }

    /**
     * @deprecated use setRecipient()
     */
    public function setTo($val)
    {
    	$this->setRecipient($val);
    }

    /**
     * Sets the recipient adress.
     */
    public function setRecipient($val)
    {
        $this->to = $val;
    }

    /**
     * Sets the recipient name.
     */
    public function setRecipientName($val)
    {
        $this->toName = $val;
    }

    /**
     * The emails subject.
     * @param string $val
     */
    public function setSubject($val)
    {
        $this->subject = $val;
    }

    /**
     * The Plain text content.
     * @param string $val
     */
    public function setContent($val)
    {
        $this->contentPlain = $val;
    }

    /**
     * Sets the HTML content.
     * Only required if you use TYPE_BOTH or TYPE_HTML.
     *
     * @param string $val
     */
    public function setHTML($val)
    {
        $this->contentHtml = $val;
    }

    /**
     * @deprecated use setFromEmail(String)
     *
     * @param string $val
     */
    public function setFrom($val)
    {
        $this->setFromEmail($val);
    }

    /**
     * Sets the sender (from) address.
     * Required field.
     *
     * @param string $val
     */
    public function setFromEmail($val)
    {
        $this->fromEmail = $val;
    }

    /**
     * Sets the senders name
     *
     * @param string $val
     */
    public function setFromName($val)
    {
        $this->fromName = $val;
    }

    /**
     * This sends the configured Email.
     *
     * @return boolean whether this Email could be send or not
     */
    public function sendMail()
    {
    	$mail = new PHPMailer(); // default is to use mail()
    	if ($this->method == BasicEmail::TRANSPORT_SENDMAIL) {
            $mail->IsSendmail(); // telling the class to use SendMail transport
    	} else if ($this->method == BasicEmail::TRANSPORT_SMTP) {
            $mail->IsSMTP();
            $mail->Host = $this->server; // SMTP server
    	} else if ($this->method == BasicEmail::TRANSPORT_QMAIL) {
            $mail->IsQmail();
    	}

    	$mail->Encoding  = $this->encoding;
    	$mail->CharSet	 = $this->charset;
        $mail->From      = $this->fromEmail;
        $mail->FromName  = $this->fromName;
        $mail->Subject   = $this->subject;

        if ($this->type == BasicEmail::TYPE_BOTH) {
            if (strlen(trim($this->contentPlain)) > 0) {
                $mail->AltBody = $this->contentPlain;
            }
            $mail->MsgHTML($this->contentHtml);
        } else if ($this->type == BasicEmail::TYPE_HTML) {
            $mail->MsgHTML($this->contentHtml);
        } else {
            $mail->Body = $this->contentPlain;
        }

        $mail->AddAddress($this->to, $this->toName);

        if ($this->reply != "") {
            $mail->AddReplyTo($this->reply);
        }

        $result = true;
        $result = $mail->Send();
        if (!$result) {
            $this->error = $mail->ErrorInfo;
        }
        if ($this->method == BasicEmail::TRANSPORT_SMTP) {
            $mail->SmtpClose();
        }
        return $result;
    }

    /**
     * Returns an error message, if send() returned false.
     * Otherwise an empty string is returned.
     *
     * @return string
     */
    public function getError()
    {
    	return $this->error;
    }

}