<?php

require_once 'application/models/Base.php';

class Application_Model_MailFetch extends Application_Model_Base
{

    private $_emailHost = 'mail.cmsoftware.be';
    private $_emailUser = 'inbox_manager@cmsoftware.be';
    private $_emailPass = 'voordewind2013';
    /**
     * Zend_Mail_Storage

     */
    private $_mail;
    /**
     * Inbox wrapper

     */
    private $_inbox = array();

    /**
     * on class instantiation we fire connect
     * to the pop server and start fetching the mails

     */
    public function __construct()
    {

        try {

            $this->_mail = new Zend_Mail_Storage_Pop3(array(
                'host' => $this->_emailHost,
                'user' => $this->_emailUser,
                'password' => $this->_emailPass));
        } catch (Exception $e) {

            var_dump($e);
            echo 'Mail Retrieval Failed...' . PHP_EOL;

            die();
        }

        $this->processMail();
    }

    private function _contentDecoder($encoding, $content)
    {

        switch ($encoding) {

            case 'quoted-printable':

                $result = quoted_printable_decode($content);

                break;

            case 'base64':

                $result = base64_decode($content);

                break;

            default:

                $result = $content;

                break;
        }

        return $result;
    }

    /**
     * This is where everything is done

     */
    public function processMail()
    {

        if ($this->_mail instanceof Zend_Mail_Storage_Pop3 && $this->_mail->countMessages() > 0) {

            $messageNum = 0;

            foreach ($this->_mail as $message) {

                $messageNum++;

                $rawMail = $this->_mail->getRawHeader($messageNum) . $this->_mail->getRawContent($messageNum);


                $received = $message->received;

                $date = date('Y-m-d H:i:s', strtotime($message->date));

                $messageId = $message->headerExists('message-id') ? $message->getHeader('message-id') : null;


//remove <> from message id

                $messageId = (preg_match('|< (.*?)>|', $messageId, $regs)) ? $regs[1] : $messageId;

                $inReplyTo = $message->headerExists('in-reply-to') ? $message->getHeader('in-reply-to') : null;

//remove <> from in-reply-to

                $inReplyTo = (preg_match('|< (.*?)>|', $inReplyTo, $regs)) ? $regs[1] : $inReplyTo;

                $references = $message->headerExists('References') ? $message->getHeader('References') : null;

//remove <> from references

                $references = (preg_match('|< (.*?)>|', $references, $regs)) ? $regs[1] : $references;

                $from = $message->from;

                $subject = $message->subject;

                $to = $message->to;

                $plainContent = null;

                $htmlContent = null;

                $attachments = array();




                /**
                 * Check if the message has multiple parts

                 */
                if ($message->isMultipart()) {

                    /**
                     * We have a multipart message
                     * lets extract the plain content,
                     * html content, and attachments

                     */
                    foreach (new RecursiveIteratorIterator($message) as $part) {

                        try {

                            switch (strtok($part->contentType, ';')) {

                                case 'text/plain':

                                    $plainContent = $this->_contentDecoder($part->getHeader('content-transfer-encoding'), $part->getContent());

                                    break;

                                case 'text/html':

                                    $htmlContent = $this->_contentDecoder($part->getHeader('content-transfer-encoding'), $part->getContent());

                                    break;

                                default: //attachment handle

                                    /*$type = strtok($part->contentType, ';');

                                    $fileName = $part->getHeader('content-description');

                                    $attachment = $this->_contentDecoder($part->getHeader('content-transfer-encoding'), $part->getContent());

                                    $attachments[] = array(
                                        'file_name' => $fileName,
                                        'type' => $type,
                                        'content' => $attachment);
                                    */

                                    break;
                            }
                        } catch (Zend_Mail_Exception $e) {

                            echo "$e \n";
                        }
                    }
                } else {

                    $plainContent = $message->getContent();
                }


                $this->_inbox[] = array('messageNumber' => $messageNum,
                    'rawMail' => $rawMail,
                    'received' => $received,
                    'date' => $date,
                    'messageId' => $messageId,
                    'inReplyTo' => $inReplyTo,
                    'references' => $references,
                    'from' => $from,
                    'subject' => $subject,
                    'to' => $to,
                    'plainContent' => $plainContent,
                    'htmlContent' => $htmlContent,
                    'attachments' => $attachments,
                    'sender' => '');

                $this->_mail->removeMessage($messageNum);
            }
        } else {

            $this->_inbox = false;
        }
    }

    public function getInbox()
    {

        return $this->_inbox;
    }

}