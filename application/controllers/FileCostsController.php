<?php

require_once 'application/controllers/BaseFileController.php';

class FileCostsController extends BaseFileController {

    public function viewAction() {

        $this->view->addButton = "/file-costs/add/index/" . $this->getParam("index");
        $this->view->printButton = true;


        if ($this->getParam("delete")) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        $sql = "select RECORD_ID,CREATION_DATE,CODE,DESCRIPTION,AMOUNT,AMOUNT_CLIENT,INVOICE_NR,INVOICEABLE,EXTRA_INFO from FILES\$FILE_COSTS_ALL_INFO where FILE_ID='{$this->fileId}' order by RECORD_ID DESC";
        $this->view->results = $this->db->get_results($sql);
    }

    public function addAction() {
        $form = new Application_Form_FileAddCost();
        $obj = new Application_Model_FilesCosts();
        $fileObj = new Application_Model_File();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $update['FILE_ID'] = $this->fileId;
                $update['AMOUNT'] = $this->functions->dbBedrag($update['AMOUNT']);
                $update['AMOUNT_CLIENT'] = $this->functions->dbBedrag($update['AMOUNT_CLIENT']);
                if (empty($update['AMOUNT'])) {
                    $update['AMOUNT'] = $fileObj->getActionCost($this->fileId, $update['COST_ID']);
                } 
                $obj->add($update);
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
            $data['INVOICEABLE'] = 1;
            $data['COST_ID'] = 1;
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
        $Obj = new Application_Model_FilesCosts();
        $Obj->delete($id);
    }

}

