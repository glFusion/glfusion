<?php
/**
 * Class to send notifications to site members via email.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @since       v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Notifiers;
use glFusion\Log\Log;
use PHPMailer\PHPMailer\PHPMailer;


/**
 * Email notification class.
 * @package shop
 */
class Email extends \glFusion\Notifier
{
    /** Array of embedded image information.
     * @var array */
    private $_embeddedImages = array();

    /** From email address.
     * @var string */
    private $_from_email = '';


    /**
     * Set any defaults and call the parent constructor.
     */
    public function __construct()
    {
        global $_CONF;
        $this->_from_email = $_CONF['noreply_mail'];
        $this->from_name = $_CONF['site_name'];
        parent::__construct();
    }


    /**
     * Override the default no-reply from email address.
     *
     * @param   string  $email  Email address
     * @return  object  $this
     */
    public function setFromEmail(string $email) : self
    {
        $this->_from_email = $email;
        return $this;
    }


    /**
     * Override the base function to include the email address.
     *
     * @param   integer $uid    User ID
     * @param   string  $name   User Name
     * @param   string  $email  Email address
     * @return  object  $this
     */
    public function addRecipient(int $uid, ?string $name=NULL, ?string $email=NULL) : self
    {
        $this->recipients[$uid] = array(
            'uid' => $uid,
            'name' => $name,
            'email' => $email,
        );
        return $this;
    }


    /**
     * Add an embedded image to an email.
     *
     * @param   array   $data   Array of image details
     * @return  object  $this
     */
    public function addEmbeddedImage(array $data) : self
    {
        $this->_embeddedImages[] = array(
            $data['file'],
            $data['name'],
            $data['filename'],
            $data['encoding'],
            $data['mime']
        );
        return $this;
    }

    /**
     * Send an email notification to the recipient.
     *
     * @return  boolean     True on success, False on error
     */
    public function send() : bool
    {
        global $_CONF, $_VARS;

        $retval = false;
        $to = array();
        $bcc = array();

        // Create the recipient list
        foreach ($this->recipients as $uid=>$data) {
            // If either email or name are missing, get them from the User table.
            if (empty($data['email']) || empty($data['name'])) {
                $U = $this->getUser($uid);
                if ($U->uid == 0) {
                    // Didn't get a valid user object
                    continue;
                }
                // Set missing values from the user object
                if (empty($data['email'])) {
                    $data['email'] = $U->email;
                }
                if (empty($data['name'])) {
                    $data['name'] = $U->fullname;
                }
            }
            // Finally, the email address must be present.
            if (!empty($data['email'])) {
                $to[] = array(
                    'email' => $data['email'],
                    'name' => $data['name'],
                );
            }
        }

        // Create the BCC recipient list
        foreach ($this->bcc as $uid=>$data) {
            // If either email or name are missing, get them from the User table.
            if (empty($data['email']) || empty($data['name'])) {
                $U = $this->getUser($uid);
                if ($U->uid == 0) {
                    // Didn't get a valid user object
                    continue;
                }
                // Set missing values from the user object
                if (empty($data['email'])) {
                    $data['email'] = $U->email;
                }
                if (empty($data['name'])) {
                    $data['name'] = $U->fullname;
                }
            }
            // Finally, the email address must be present.
            if (!empty($data['email'])) {
                $bcc[] = array(
                    'email' => $data['email'],
                    'name' => $data['name'],
                );
            }
        }

        if (!empty($to) || !empty($bcc)) {
            $msgData = array(
                'to' => $to,
                'bcc' => $bcc,
                'from' => $this->_from_email,
                'htmlmessage' => $this->htmlmessage,
                'textmessage' => $this->textmessage,
                'subject' => $this->subject,
            );
            if (!empty($this->_embeddedImages)) {
                $msgData['embeddedImage'] = $this->_embeddedImages;
            }
            $retval = self::sendNotification($msgData);
        }
        return $retval;
    }


    /**
     * A notification system that is a bit kinder to the mail server.
     *
     * @param   array   $msgData    Array of message data
     * @return  bool    True on success, False on error
     */
    public static function sendNotification(array $msgData=array()) : bool
    {
        global $_CONF, $_VARS;

        // define the maximum number of emails allowed per bcc
        $maxEmailsPerSend = 10;

        // ensure we have something to send...
        if ( !isset($msgData['htmlmessage']) && !isset($msgData['textmessage']) ) {
            Log::write('system',Log::WARNING,"COM_emailNotification() - No message text was provided - nothing to send.");
            return false; // no message defined
        }
        if ( empty($msgData['htmlmessage']) && empty($msgData['textmessage']) ) {
            Log::write('system',Log::ERROR,"COM_emailNotification() - Empty message data provided");
            return false; // no text in either...
        }
        if ( !isset($msgData['subject']) || empty($msgData['subject']) ) {
            Log::write('system',Log::WARNING,"COM_emailNotification() - No email subject was provided - not sending notification.");
            return false; // must have a subject
        }

        $queued = 0;

        $subject = substr( $msgData['subject'], 0, strcspn( $msgData['subject'], "\r\n" ));
        $subject = COM_emailEscape( $subject );

        $mail = new PHPMailer();
        $mail->SetLanguage('en');
        $mail->CharSet = COM_getCharset();
        if ($_CONF['mail_backend'] == 'smtp' ) {
            $mail->IsSMTP();
            $mail->Host     = $_CONF['mail_smtp_host'];
            $mail->Port     = $_CONF['mail_smtp_port'];
            if ( $_CONF['mail_smtp_secure'] != 'none' ) {
                $mail->SMTPSecure = $_CONF['mail_smtp_secure'];
            }
            if ( $_CONF['mail_smtp_auth'] ) {
                $mail->SMTPAuth   = true;
                $mail->Username = $_CONF['mail_smtp_username'];
                $mail->Password = $_CONF['mail_smtp_password'];
            }
            $mail->Mailer = "smtp";
        } elseif ($_CONF['mail_backend'] == 'sendmail') {
            $mail->Mailer = "sendmail";
            $mail->Sendmail = $_CONF['mail_sendmail_path'];
        } else {
            $mail->Mailer = "mail";
        }
        $mail->WordWrap = 76;

        if ( isset($msgData['htmlmessage']) && !empty($msgData['htmlmessage']) ) {
            $mail->IsHTML(true);
            $mail->Body = $msgData['htmlmessage'];
            if ( isset($msgData['textmessage']) && !empty($msgData['textmessage']) ) {
                $mail->AltBody = $msgData['textmessage'];
            }
        } else {
            $mail->IsHTML(false);
            if ( isset($msgData['textmessage']) && !empty($msgData['textmessage']) ) {
                $mail->Body = $msgData['textmessage'];
            }
        }
        $mail->Subject = $subject;

        if ( isset($msgData['embeddedImage']) && is_array($msgData['embeddedImage'])) {
            foreach ($msgData['embeddedImage'] AS $embeddedImage ) {
                $mail->AddEmbeddedImage(
                    $embeddedImage['file'],
                    $embeddedImage['name'],
                    $embeddedImage['filename'],
                    $embeddedImage['encoding'],
                    $embeddedImage['mime']
                );
            }
        }

        if ( is_array($msgData['from'])) {
            if ( filter_var($msgData['from']['email'], FILTER_VALIDATE_EMAIL) ) {
                $mail->From = $msgData['from']['email'];
            } else {
                $mail->From = $_CONF['noreply_mail'];
            }
            $mail->FromName = $msgData['from']['name'];
        } else {
            if ( filter_var($msgData['from'], FILTER_VALIDATE_EMAIL) ) {
                $mail->From = $msgData['from'];
            } else {
                $mail->From = $_CONF['noreply_mail'];
            }
            $mail->FromName = $_CONF['site_name'];
        }

        if (isset($msgData['to'])) {
            if ( is_array($msgData['to']) ) {
                foreach ($msgData['to'] AS $to) {
                    if ( is_array($to) ) {
                        if ( filter_var($to['email'], FILTER_VALIDATE_EMAIL) ) {
                            $mail->addAddress($to['email'],$to['name']);
                        }
                    } else {
                        if ( COM_isEmail($to) ) {
                            if ( filter_var($to, FILTER_VALIDATE_EMAIL) ) {
                                $mail->addAddress($to);
                            }
                        }
                    }

                    $queued++;
                    if ( $queued >= $maxEmailsPerSend ) {
                        if (!$mail->Send()) {
                            Log::write('system',Log::ERROR,"Send Email returned: " . $mail->ErrorInfo);
                        }
                        $queued = 0;
                        $mail->ClearBCCs();
                    }
                }
            } else {
                // Compatibility with single-address COM_mail()
                $msgData['to'] = array(
                    array(
                        'email' => $msgData['to'],
                        'name' => '',
                    )
                );
            }
        }

        if (isset($msgData['bcc'])) {
            if ( is_array($msgData['bcc']) ) {
                foreach ($msgData['bcc'] AS $to) {
                    if ( is_array($to) ) {
                        if ( filter_var($to['email'], FILTER_VALIDATE_EMAIL) ) {
                            $mail->addBCC($to['email'],$to['name']);
                        }
                    } else {
                        if ( COM_isEmail($to) ) {
                            if ( filter_var($to, FILTER_VALIDATE_EMAIL) ) {
                                $mail->addBCC($to);
                            }
                        }
                    }

                    $queued++;
                    if ( $queued >= $maxEmailsPerSend ) {
                        if (!$mail->Send()) {
                            Log::write('system',Log::ERROR,"Send Email returned: " . $mail->ErrorInfo);
                        }
                        $queued = 0;
                        $mail->ClearBCCs();
                    }
                }
            }
        }

        if ( $queued > 0 ) {
            if ( !@$mail->Send() ) {
                Log::write('system',Log::ERROR,"Send Email returned: " . $mail->ErrorInfo);
                return false;
            }
        }
        return true;
    }

}

