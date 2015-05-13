<?php

require_once 'application/controllers/BaseController.php';

class InvoicesController extends BaseController
{

    public function searchAction() {

        $this->checkAccessAndRedirect(array('invoices/search'));

        $maxRecords = 500;
        $this->view->bread = $this->functions->T("menu_general") . "->" . $this->functions->T("menu_invoices_search");

        $this->view->exportButton = true;
        $this->view->printButton = true;

        $rights = new Application_Model_UserRights();


        $form = new Application_Form_Invoices();
        $this->view->SearchBox = $form;


        if ($this->auth->online_rights != 5) {
            $this->view->showClient = true;
        }
        if ($this->auth->online_rights != 5 && $this->auth->online_rights != 6) {
            $this->view->showCollector = true;
        }

        $session = new Zend_Session_Namespace('INVOICES');

        $extraQuery = "";

        if (empty($session->orderby)) {
            $session->orderby = "INVOICE_REFERENCE";
        }
        if (empty($session->order)) {
            $session->orde = "ASC";
        }


        if ($this->getParam('orderby')) {
            $session->orderby = $this->getParam('orderby');
            if ($this->getParam('orderby') == $session->orderby) {
                $session->order = ($session->order == "DESC") ? "ASC" : "DESC";
            }
        }


        if ($form->isValid($_POST) && $this->getParam('formSubmit')) {
            $data = $form->getValues();
            $session->data = $data;
        }
        if (!empty($session->data)) {
            $form->populate($session->data);
        }


        $query_extra = "";
        $query_files = "";

        if (!empty($session->data)) {
            if ($session->data['client'] != "") {
                $query_extra = " and F.CLIENT_NAME CONTAINING  '{$session->data['client']}'";
            }
            if (!empty($session->data['debtor_name'])) {
                $query_extra .= " and F.DEBTOR_NAME CONTAINING '" . $session->data['debtor_name'] . "'";
            }
            if (!empty($session->data['invoice_type'])) {
                $query_extra .= " and I.REFERENCE_TYPE CONTAINING '" . $session->data['invoice_type'] . "'";
            }
            if ($session->data['client_reference'] != "")
                $query_extra .= " and F.REFERENCE CONTAINING '{$session->data['client_reference']}' ";
            if ($session->data['invoice'] != "") {
                $query_extra .= " and I.REFERENCE CONTAINING '{$session->data['invoice']}'";
            }
            if ($session->data['collector'] != "") {
                $query_extra .= " and F.COLLECTOR_ID = '{$session->data['collector']}' ";
            }
            if ($session->data['state_id'] != "") {
                $query_extra .= " and I.STATE_ID = '{$session->data['state_id']}' ";
            }
            if ($session->data['debtor'] != "") {
                $query_extra .= " and F.DEBTOR_NAME CONTAINING '{$session->data['debtor']}' ";
            }

            if (!empty($session->data['dispute'])) {
                if ($session->data['dispute'] == 'Y') {
                    $query_extra .= " and I.DISPUTE=1";
                }
                if ($session->data['dispute'] == 'N') {
                    $query_extra .= " and I.DISPUTE=0";
                }
            }

            if ($query_extra == "") {
                $query_extra = "AND 1=1";
            }

            if ($data['extra_text'] != "") {
                if (stripos($data['extra_field'], "DATE") !== false) {
                    $session->data['extra_text'] = $this->functions->date_dbformat($session->data['extra_text']);
                }
                if (is_numeric($this->functions->dbBedrag($session->data['extra_text']))) {
                    $session->data['extra_text'] = $this->functions->dbBedrag($session->data['extra_text']);
                }


                if (!isset($extra_compare_query)) {
                    $extra_compare_query = "";
                }
                $extra_compare_query .= " and I.{$session->data['extra_field']} {$session->data['extra_compare']} '{$session->data['extra_text']}' ";
                $query_extra .= "$extra_compare_query";
            }
        }

        if ($this->auth->online_rights == 7) {
            $query_extra .= " and A.COLLECTOR_ID = '{$this->auth->online_collector_id}' AND COLLECTOR_VISIBLE = 1";
        }

        if ($this->auth->online_rights == 5) {
            if (empty($this->auth->online_subclients)) {
                $query_extra .= " and F.CLIENT_ID = '{$this->auth->online_client_id}' ";
            } else {
                $query_extra .= " AND (F.CLIENT_ID = {$this->auth->online_client_id} ";
                foreach ($this->auth->online_subclients as $value) {
                    $query_extra .= " OR F.CLIENT_ID = $value";
                }
                $query_extra .= ")";
            }
        }

        if ($this->auth->online_rights == 6) {
            $query_extra .= " and F.COLLECTOR_ID = '{$this->auth->online_collector_id}' ";
        }
        $sql = "SELECT COUNT(*) AS COUNTER,SUM(I.AMOUNT+I.INTEREST+I.COSTS) AS TOTAL, SUM(I.SALDO_AMOUNT+I.SALDO_INTEREST+I.SALDO_COSTS) AS PAYABLE  FROM FILES\$REFERENCES I
              JOIN  FILES\$FILES_ALL_INFO F ON F.FILE_ID=I.FILE_ID
              JOIN FILES\$STATES S ON S.STATE_ID = F.STATE_ID  WHERE 1=1 {$query_extra}";
        $totals = $this->db->get_row($sql);
        $this->view->totals = $totals;

        $sql = "SELECT DISTINCT F.REFERENCE AS CLIENT_REFERENCE,I.REFERENCE_TYPE,F.FILE_ID,F.CLIENT_NAME,I.INVOICE_DATE,S.CODE AS STATE_CODE,I.REFERENCE AS INVOICE_REFERENCE,F.COLLECTOR_CODE,I.AMOUNT,I.INTEREST,I.COSTS,(I.AMOUNT + I.COSTS + I.INTEREST) AS TOTAL
              ,I.PAYED_AMOUNT,I.PAYED_INTEREST,I.PAYED_COSTS,(I.SALDO_AMOUNT + I.SALDO_INTEREST + I.SALDO_COSTS) AS PAYABLE,F.DEBTOR_NAME,F.DEBTOR_VAT_NR,F.DEBTOR_BIRTH_DAY,F.DEBTOR_ADDRESS,F.DEBTOR_ZIP_CODE,F.DEBTOR_CITY,
              F.DEBTOR_LANGUAGE_CODE, I.START_DATE, I.DISPUTE, I.DISPUTE_DATE, I.DISPUTE_DUEDATE, I.DISPUTE_ENDED_DATE FROM FILES\$REFERENCES I
              JOIN  FILES\$FILES_ALL_INFO F ON F.FILE_ID=I.FILE_ID
              JOIN FILES\$STATES S ON S.STATE_ID = I.STATE_ID {$query_extra} order by {$session->orderby} {$session->order}";

        if ($totals->COUNTER > $maxRecords) {
            $sql = str_replace("SELECT ", "SELECT FIRST {$maxRecords} ", $sql);
            $this->view->onlyFirst = $maxRecords;
        }
        $results = $this->db->get_results($sql);
        if (!empty($results)) {
            $sqlExport = str_replace("SELECT FIRST {$maxRecords} ","SELECT ", $sql);
            $this->export->sql = $sqlExport;
            $sql = "SELECT FILE_ID,FILE_NR,DEBTOR_NAME FROM FILES\$FILES_ALL_INFO A WHERE 1=1 {$query_extra} order by {$session->orderby} {$session->order}";
            //$session->fileList = $this->db->get_results($sql, ARRAY_A);
            $this->view->results = $results;
        } else {
            $this->export->sql = "";
            $this->view->exportButton = false;
        }

        $filesReferencesModel = new Application_Model_FilesReferences();
        $this->view->totalNotDue = $filesReferencesModel->getTotalNotDue();
        $this->view->totalPastDue = $filesReferencesModel->getTotalPastDue();
    }

    public function addAction()
    {


        $clientObj = new Application_Model_Clients();

        $this->view->hideTop = true;

        $debtorForm = new Application_Form_AddDebtor();
        $this->view->debtorForm = $debtorForm;
        $clientForm = new Application_Form_AddClient();
        $this->view->clientForm = $clientForm;


        if ($this->moduleAccess('binformation')) {
            $this->view->binfo = true;
            $bInfoForm = new Application_Form_bInformation();
            $this->view->bInfoForm = $bInfoForm;

            $id = $this->getParam("binfo");

            if (!empty($id)) {
                $client = new Application_Model_Binformation();
                $xml = $client->OrderReport($id);
                $report = simplexml_load_string($xml);

                if (empty($report)) {
                    $this->view->showBinfoNoReport = true;
                }

                $data = array(
                    'NAME' => $report->descriptive->name,
                    'VATNR' => $report->descriptive->vat,
                    'ADDRESS' => $report->descriptive->address->raw_street,
                    'ADDRESS' => $report->descriptive->address->raw_street,
                    'ZIP_CODE' => $report->descriptive->address->zip,
                    'CITY' => $report->descriptive->address->locality,
                    'TELEPHONE' => $report->descriptive->contact->phone,
                    'TELEFAX' => $report->descriptive->contact->fax,
                    'E_MAIL' => $report->descriptive->contact->email,

                );

                $debtorForm->populate($data);
            }


        }


        if ($this->auth->online_rights == 5) {
            $clientForm->client_name->setAttrib('disabled', 'disabled');
        }

        $invoiceForm = new Application_Form_AddInvoices();
        $this->view->invoiceForm = $invoiceForm;

        if ($this->getParam("sessionId")) {
            $sessionId = $this->getParam("sessionId");
        } else {
            $sessionId = "ADDFILE" . rand(0, 9999);
        }
        $this->view->sessionId = $sessionId;
        $session = new Zend_Session_Namespace($sessionId);

        if ($this->getParam("deleteInvoice") !== false && is_array($session->invoices)) {
            if (key_exists($this->getParam("deleteInvoice"), $session->invoices)) {
                unset($session->invoices[$this->getParam("deleteInvoice")]);
                $this->view->showInvoicesDeleted = true;

            }
        }
        if ($this->getParam("debtorForm")) {
            if ($debtorForm->isValid($_POST)) {
                $data = $debtorForm->getValues();
                $data['birth_day'] = $this->functions->date_dbformat($data['birth_day']);
                $session->debtor = $data;
                $session->debtorOk = 1;
                $this->view->showDebtorSaved = true;
            } else {
                $this->view->showDebtorError = true;
                $this->view->errors = $debtorForm->getErrors();
                $session->debtorOk = 0;
            }
        } else {
            if (!empty($session->debtor)) {
                $debtorForm->populate($session->debtor);
            }
        }
        if ($this->getParam("clientForm")) {
            if ($this->auth->online_rights == 5) {
                $_POST['client_id'] = $this->auth->online_client_id;
                $_POST['client_name'] = $clientObj->getClientField($this->auth->online_client_id, 'NAME');
            }
            if ($clientForm->isValid($_POST)) {
                $data = $clientForm->getValues();
                if (!empty($data['client_id'])) {
                    $name = $clientObj->getClientField($data['client_id'], 'NAME');
                    if ($name != $data['client_name']) {
                        $this->view->showIncorrectClientError = true;
                        $this->view->errors = $clientForm->getErrors();
                        $session->clientOk = 0;
                    }
                }
                $session->client = $data;
                $session->clientOk = 1;
                $this->view->showClientSaved = true;
            } else {
                $this->view->showClientError = true;
                $this->view->errors = $clientForm->getErrors();
                $session->clientOk = 0;
            }
        } else {
            if (!empty($session->client)) {
                $clientForm->populate($session->client);
            } else {
                if ($this->auth->online_rights == 5) {
                    $data['client_id'] = $this->auth->online_client_id;
                    $data['client_name'] = $clientObj->getClientField($this->auth->online_client_id, 'NAME');
                    $clientForm->populate($data);
                }

            }

        }
        if ($this->getParam("invoiceForm")) {
            if ($invoiceForm->isValid($_POST)) {
                $data = $invoiceForm->getValues();
                $data['amount'] = $this->functions->dbBedrag($data['amount']);
                $data['invoice_date'] = $this->functions->date_dbformat($data['invoice_date']);
                $data['start_date'] = $this->functions->date_dbformat($data['start_date']);
                $session->invoices[] = $data;
                $data = array();
                $invoiceForm->populate($data);
                $this->view->invoiceForm = $invoiceForm;
                $this->view->showInvoicesSaved = true;

            } else {
                $this->view->showInvoiceError = true;
                $this->view->errors = $invoiceForm->getErrors();
            }
        }

        if (!empty($session->invoices)) {
            $this->view->invoices = $session->invoices;
        }

        if ($this->getParam("debtorForm")) {
            $this->view->activeTab = 0;
        }
        if ($this->getParam("clientForm")) {
            $this->view->activeTab = 1;
        }
        if ($this->getParam("invoiceForm") or $this->getParam("deleteForm")) {
            $this->view->activeTab = 2;
        }

        if ($session->debtorOk == 1 && $session->clientOk == 1 && !empty($session->invoices)) {
            $this->view->disabledTab = "";

            $amount = 0;
            foreach ($session->invoices as $invoice) {
                $amount += $invoice['amount'];
            }
            $session->totalAmount = $amount;
        } else {
            if (empty($this->view->activeTab)) {
                $this->view->activeTab = 0;
            }
            $this->view->disabledTab = ", disabled : [3]";
        }

        $this->view->session = $session;
    }

    public function createAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        if ($this->getParam("sessionId")) {
            $sessionId = $this->getParam("sessionId");
        } else {
            $sessionId = "ADDFILE" . rand(0, 9999);
        }
        $this->view->sessionId = $sessionId;
        $session = new Zend_Session_Namespace($sessionId);

        $filesModel = new Application_Model_Files();
        $debtorsModel = new Application_Model_Debtors();


        if ($session->debtorOk == 1 && $session->clientOk == 1 && !empty($session->invoices)) {
            if (empty($session->debtor['DEBTOR_ID'])) {
                $data = $session->debtor;
                $debtorId = $debtorsModel->create($data);
            } else {
                $debtorId = $session->debtor['DEBTOR_ID'];
                $data = $session->debtor;
                $debtorsModel->update($data);
            }
            $data = array(
                'CLIENT_ID' => $session->client['client_id'],
                'DEBTOR_ID' => $debtorId,
                'REFERENCE' => $session->client['client_reference'],
                'invoices' => $session->invoices,
                'STATE_ID' => $this->functions->getSetting('factuur_aanmaak_status'),
                'COLLECTOR_ID' => $this->functions->getSetting('algemeen_collector_id'),
                'FILE_NR' => $filesModel->getNextFileNr(),
            );


            $fileId = $filesModel->create($data);

            $sessionFiles = new Zend_Session_Namespace("FILES");
            $sql = "SELECT FILE_ID,FILE_NR,DEBTOR_NAME FROM FILES\$FILES_ALL_INFO A WHERE FILE_ID = {$fileId}";
            $sessionFiles->fileList = $this->db->get_results($sql, ARRAY_A);
            $this->_redirect("/file-detail/view/fileId/{$fileId}");
        }
    }

    private function _delete($fileId)
    {
        $Obj = new Application_Model_Files();
        $queries = $Obj->deleteFile($fileId, true);
    }

}

