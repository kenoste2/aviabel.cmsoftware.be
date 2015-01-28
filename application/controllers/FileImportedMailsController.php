<?php

require_once 'application/controllers/BaseFileController.php';

class FileImportedMailsController extends BaseFileController {

    public function viewAction() {
        $importedMails = new Application_Model_ImportedMails();
        $this->view->importedMails = $importedMails->retrieveImportedMailsByFileId($this->fileId);
    }
}
