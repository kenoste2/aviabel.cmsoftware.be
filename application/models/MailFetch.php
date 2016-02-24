<?php

require_once 'application/models/Base.php';

class Application_Model_MailFetch extends Application_Model_Base
{
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

        global $config;

        try {

            $this->_mail = new Zend_Mail_Storage_Pop3(array(
                'host' => $config->inboxHost,
                'user' => $config->inboxAccount,
                'password' => $config->inboxPass,
                'ssl'      => 'SSL'
            ));
        } catch (Exception $e) {
           print "<pre>";
            var_dump($e->getMessage());
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

                $from = utf8_encode($message->from);


                if (!empty($message->subject)) {
                $subject = utf8_encode($message->subject);
                } else {
                    $subject = "";
                }


                $subject = utf8_encode($subject);

                $to = utf8_encode($message->to);

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

                                    if (in_array('content-transfer-encoding', $part->getHeaders())) {
                                        $encoding = $part->getHeader('content-transfer-encoding');
                                    } else {
                                        $encoding = "quoted-printable";
                                    }


                                    $plainContent = $this->_contentDecoder($encoding, $part->getContent());

                                    break;

                                case 'text/html':

                                    $htmlContent = $this->_contentDecoder($part->getHeader('content-transfer-encoding'), $part->getContent());

                                    break;

                                default: //attachment handle

                                    $type = strtok($part->contentType, ';');

                                    $headersArray  = $part->getHeaders();

                                    if (array_key_exists("content-disposition", $headersArray)) {
                                    $fileNameHeader = $part->getHeader('content-disposition');
                                    } elseif (array_key_exists("content-type", $headersArray)) {
                                        $fileNameHeader = $part->getHeader('content-type');
                                    }

                                    if (stripos($fileNameHeader, " name=") !== false) {
                                        $fileNameHeader = str_replace(" name=", " filename=", $fileNameHeader);
                                    }

                                    $fileNameMatches = array();
                                    preg_match("/filename=\"?(.+?)\"/", $fileNameHeader, $fileNameMatches);
                                    $fileName = $fileNameHeader;
                                    if(count($fileNameMatches) > 1) {
                                        $fileName = $fileNameMatches[1];
                                    }

                                    $attachment = $this->_contentDecoder($part->getHeader('content-transfer-encoding'), $part->getContent());

                                    $attachments[] = array(
                                        'file_name' => $fileName,
                                        'type' => $type,
                                        'content' => $attachment);

                                    break;
                            }
                        } catch (Zend_Mail_Exception $e) {

                            print "<pre>";
                            echo "$e \n";
                            print "</pre>";
                        }
                    }
                } else {

                    $plainContent = $message->getContent();
                }


                $to = str_replace("<"," ",$to);
                $to = str_replace(">"," ",$to);
                $from = str_replace("(","",$from);
                $from = str_replace(")","",$from);

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