<?php

require_once 'application/controllers/BaseFileController.php';

class FilePaymentsController extends BaseFileController {

    public function viewAction() {
        $this->view->printButton = true;
        if ($this->hasAccess('addPayments')) {
            $this->view->addButton = "/file-payments/add/fileId/" . $this->fileId;
        }
        if ($this->hasAccess('managePayments')) {
            $this->view->managePayments = true;
        }



        if ($this->getParam("delete")) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        $sql = "select INVOICED,ACCOUNT_DESCRIPTION,COMMISSION,PAYMENT_ID,CREATION_DATE,PAYMENT_DATE,AMOUNT,PAYMENT_FOR,REFERENCE,REFUND_STATEMENT,INVOICEABLE,INVOICE_ID,WITH_COMMISSION,INVOICE_NR,ACCOUNT_CODE,JOURNAL_DESCRIPTION,COLLECTOR_INVOICEABLE,ACCOUNT_ID,COLLECTOR_INVOICE_ID,COLLECTOR_WITH_COMMISSION
                from FILES\$PAYMENTS_ALL_INFO where FILE_ID='$this->fileId' order by PAYMENT_DATE DESC";
        $this->view->results = $this->db->get_results($sql);
    }

    public function addAction() {
        $form = new Application_Form_FileAddPayment();
        $filePaymentObj = new Application_Model_FilesPayments();

        $references = $this->db->get_results("select REFERENCE_ID,REFERENCE from FILES\$REFERENCES WHERE FILE_ID = {$this->fileId} order by REFERENCE", ARRAY_N);
        $form->REFERENCE_ID->setMultiOptions($this->functions->db2array($references));

        if ($this->auth->online_rights == 5) {
            $form->removeElement('ACCOUNT_ID');
        }

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                if ($this->auth->online_rights == 5) {
                    $data['ACCOUNT_ID'] = 2;
                }
                $update['FILE_ID'] = $this->fileId;
                $update['VALUTA_DATE'] = $this->functions->date_dbformat($update['VALUTA_DATE']);
                $update['AMOUNT'] = $this->functions->dbBedrag($update['AMOUNT']);
                if ($this->auth->online_rights == 5) {
                    $update['ACCOUNT_ID'] = 2;
                }

                    $filePaymentObj->addPayment($this->fileId, $update['AMOUNT'], $update['ACCOUNT_ID'], $update['VALUTA_DATE'], $update['DESCRIPTION']
                        , $data['REFERENCE_ID']);
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
            if ($this->auth->online_rights != 5) {
                $data['ACCOUNT_ID'] = 1;
            }
            $data['VALUTA_DATE'] = date("d/m/Y");
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
                $update['COMMISSION'] = $this->functions->dbBedrag($update['COMMISSION']);
                $filePaymentObj->save($update,"PAYMENT_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $this->db->get_row("SELECT * FROM FILES\$PAYMENTS WHERE PAYMENT_ID = {$this->getParam('id')}");
            $data = array();
            $data['PAYMENT_DATE'] = $this->functions->dateformat($row->PAYMENT_DATE);
            $data['COMMISSION'] = $this->functions->amount($row->COMMISSION);
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_FilesPayments();
        $Obj->delete($id);
    }

}

