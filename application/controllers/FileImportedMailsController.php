<?php

require_once 'application/controllers/BaseFileController.php';

class FileImportedMailsController extends BaseFileController {

    public function viewAction() {
        global $config;
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
}
