<?php

require_once 'application/controllers/BaseController.php';

class ImportedMailController extends BaseController {

    public function overviewAction() {
        global $config;

        $this->checkAccessAndRedirect(array('imported-mail/overview'));

        $form = new Application_Form_MailOverview();

        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                $this->loadFromForm($form);
            }
        } else {
            $this->loadFromForm($form);
        }

        $this->view->pageRootUrl = $config->rootLocation;
        $this->view->form = $form;
    }

    public function downloadAttachmentAction() {
        global $config;

        $id = $this->getParam('index');
        $importedMails = new Application_Model_ImportedMails();
        $attachment = $importedMails->retrieveAttachmentById($id);
        if($attachment) {
            header("Content-Type: {$attachment->MIME_TYPE}");
            header("Content-Disposition: attachment; filename=\"{$attachment->ORIGINAL_FILENAME}\"");
            $filePath = "{$config->rootMailAttachmentsDocuments}/{$attachment->SERVER_FILENAME}";
            readfile($filePath);
        } else {
            die('No file found');
        }

        // disable layout and view
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * @param $form
     */
    private function loadFromForm($form)
    {
        $importedMailsModel = new Application_Model_ImportedMails();
        $fromDate = $form->getValue("FROM_DATE");
        $toDate = $form->getValue("TO_DATE");
        $mails = $importedMailsModel->retrieveByDateRange($this->functions->date_dbformat($fromDate), $this->functions->date_dbformat($toDate));
        $this->view->mails = $mails;
    }
}
