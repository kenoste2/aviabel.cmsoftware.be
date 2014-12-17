<?php

require_once 'application/controllers/BaseFileController.php';

class FileDocumentsController extends BaseFileController {

    public function viewAction() {
        
        global $config;

        if ($this->hasAccess('addDocuments')) {
            $this->view->addButton = "/file-documents/add/index/" . $this->getParam("index");
        }
        if ($this->hasAccess('deleteAllDocuments')) {
            $this->view->MayDelete = true;
        }

        $this->view->printButton = true;


        if ($this->getParam("delete")) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        if ($this->auth->online_rights != 4) {
            $extra_query = " AND VISIBLE = '1'";
        }
        $sql = "SELECT * FROM FILE_DOCUMENTS WHERE FILE_ID={$this->fileId} $extra_query ORDER BY FILENAME";
        $this->view->results = $this->db->get_results($sql);
        
        
        $this->view->locationFiles = $config->rootLocation.$config->MapFileDocuments;
        $this->view->online_user = $this->auth->online_user;
    }

    public function addAction() {
        $form = new Application_Form_FileAddDocuments();
        $obj = new Application_Model_FilesDocuments();


        if ($this->auth->online_rights == 5) {
            $form->removeElement('VISIBLE');
        }

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();

                if ($this->auth->online_rights == 5) {
                    $update['VISIBLE'] = 1;
                }

                for ($i = 1; $i <= 5; $i++) {
                    $fileName = 'userfile' . $i;
                    if (!empty($update[$fileName])) {
                        $obj->add($this->fileId, $form->$fileName, $update['DESCRIPTION'],$update['VISIBLE']);
                    }
                }
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
            $data['VISIBLE'] = 1;
        }
        // Populating form
        $form->populate($data);
        $this->view->form = $form;
    }

    public function editAction() {
        $form = new Application_Form_FileEditPayment();
        $filePaymentObj = new Application_Model_FilesPayments();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $update['PAYMENT_DATE'] = $this->functions->date_dbformat($update['PAYMENT_DATE']);
                $update['AMOUNT'] = $this->functions->dbBedrag($update['AMOUNT']);
                $update['COMMISSION'] = $this->functions->dbBedrag($update['COMMISSION']);
                $filePaymentObj->save($update, "PAYMENT_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $this->db->get_row("SELECT * FROM FILES\$PAYMENTS WHERE PAYMENT_ID = {$this->getParam('id')}");
            $data = array();
            $data['PAYMENT_DATE'] = $this->functions->dateformat($row->PAYMENT_DATE);
            $data['AMOUNT'] = $this->functions->amount($row->AMOUNT);
            $data['COMMISSION'] = $this->functions->amount($row->COMMISSION);
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_FilesDocuments();
        $Obj->delete($id);
    }

}

