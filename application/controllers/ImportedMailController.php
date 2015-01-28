<?php

require_once 'application/controllers/BaseController.php';

class ImportedMailController extends BaseController {

    public function overviewAction() {
        global $config;

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
