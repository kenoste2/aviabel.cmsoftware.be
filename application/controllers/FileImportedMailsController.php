<?php

require_once 'application/controllers/BaseFileController.php';

class FileImportedMailsController extends BaseFileController {

    public function viewAction() {
        global $config;

        $this->view->addButton = "/file-imported-mails/send-email/fileId/{$this->fileId}";

        $importedMails = new Application_Model_ImportedMails();
        $mails = $importedMails->retrieveImportedMailsByFileId($this->fileId);
        $finalMails = array();

        foreach($mails as $mail) {
            $finalMail = (array) $mail;
            $finalMail['attachments'] = $importedMails->retrieveAttachmentsByMailId($mail->IMPORTED_MAIL_ID);
            $finalMails []= $finalMail;
        }

        $this->view->pageRootUrl = $config->rootLocation;
        $this->view->importedMails = $finalMails;
    }

    public function sendEmailAction() {
        global $config;

        $form = new Application_Form_SendEmail($this->fileId);

        if ($this->getRequest()->isPost()) {

            if($form->isValid($this->getRequest()->getPost())) {
                $messages =  $this->sendEmailFromForm($form);
                $this->view->messages = $messages;
                $this->view->success = count($messages) <= 0;
            }
        }

        $this->view->form = $form;
        $this->view->emailHost = $config->defaultEmailHost;
    }

    public function forwardEmailAction() {
        global $config;

        $emailId = $this->getParam("email-id");

        $form = new Application_Form_ForwardEmail($emailId);

        if ($this->getRequest()->isPost()) {

            if($form->isValid($this->getRequest()->getPost())) {
                $messages =  $this->sendEmailFromForm($form);
                $this->view->messages = $messages;
                $this->view->success = count($messages) <= 0;
            }
        }

        $this->view->form = $form;
        $this->view->emailHost = $config->defaultEmailHost;

    }

    /**
    +     * @param $form
    +     * @return array
    +     */
    public function sendEmailFromForm($form) {
        global $config;

        $mailsObj = new Application_Model_Mail();
        $to = $form->getValue("TO");

        $fromType = $form->getValue("FROM");

        $filesObj = new Application_Model_Files();
        $fileObj = new Application_Model_File();

        if($fromType === "CUSTOM") {
            $baseFrom = $form->getValue("CUSTOM_FROM");
            $fromEmail = "{$baseFrom}@{$config->defaultEmailHost}";
        } else if ($fromType === "CLIENT") {
            $clientObj = new Application_Model_Clients();
            $file = $fileObj->getFileData($this->fileId);
            $client = $clientObj->getClientIdById($file->CLIENT_ID);
            $fromEmail = $client->E_MAIL;

        } else {
            $userObj = new Application_Model_Users();
            $user = $userObj->getLoggedInUser();
            $fromEmail = $user->E_MAIL;
        }

        $messages = array();
        if(!$fromEmail || trim($fromEmail) == "") {
            $messages []= $this->view->G("no_email_c");
        }

        $from = array('email' => $fromEmail, 'name' => $fromEmail);
        $baseSubject = $form->getValue("SUBJECT");
        $fileNr = $filesObj->getFileNumberById($this->fileId);
        $subject = "{$baseSubject} #{$fileNr}#";
        $content = $form->getValue("CONTENT");

        $attachments = $this->addFileDocumentsAsAttachments($form, $config);

        $attachments = array_merge($attachments, $this->getActionDocumentsAsAttachments($form, $config));

        $attachments = array_merge($attachments, $this->getEmailDocumentsAsAttachments($form, $config));

        $bcc = Application_Model_MailFetch::$emailUser;

        try {

            if(count($messages) <= 0) {
                $mailsObj->sendMail($to, $subject, $content, $content, $attachments, $from, false, $bcc, true);
            }
        } catch(Zend_Mail_Transport_Exception $ex) {
            $messages []= $this->view->G("mail_not_sent_c");
        }
        return $messages;
    }

    /**
     * @param $form
     * @param $config
     * @return array
     */
    public function addFileDocumentsAsAttachments($form, $config)
    {
        $documentIds = $form->getValue("DOCUMENTS");
        $filesDocumentsObj = new Application_Model_FilesDocuments();
        $filesDocuments = $filesDocumentsObj->getDocumentsByIds($documentIds);

        $attachments = array();
        if (count($filesDocuments) > 0) {
            foreach ($filesDocuments as $filesDocument) {
                $filename = "{$config->rootFileDocuments}/{$filesDocument->FILENAME}";
                $attachments[] = array('content' => file_get_contents($filename), 'filename' => basename($filename));
            }
            return $attachments;
        }
        return $attachments;
    }

    /**
     * @param $form
     * @param $config
     * @return array
     */
    public function getEmailDocumentsAsAttachments($form, $config)
    {
        $importedMailAttachmentIds = $form->getValue("EMAIL_DOCUMENTS");
        $importedMailObj = new Application_Model_ImportedMails();
        $importedMailAttachments = $importedMailObj->retrieveAttachmentsById($importedMailAttachmentIds);
        $attachments = array();
        if (count($importedMailAttachments) > 0) {
            foreach ($importedMailAttachments as $attachment) {
                $filename = "{$config->rootMailAttachmentsDocuments}/{$attachment->SERVER_FILENAME}";
                $attachments[] = array('content' => file_get_contents($filename), 'filename' => basename($filename));
            }
            return $attachments;
        }
        return $attachments;
    }


    public function getActionDocumentsAsAttachments($form, $config)
    {
        $fileActionIds = $form->getValue("ACTION_DOCUMENTS");
        $attachments = array();
        if (count($fileActionIds) > 0) {
            foreach ($fileActionIds as $fileActionId) {
                $filename =  "{$config->rootFileActionDocuments}/{$fileActionId}.pdf";
                $attachments[] = array('content' => file_get_contents($filename), 'filename' => basename($filename));
            }
            return $attachments;
        }
        return $attachments;
    }


}
