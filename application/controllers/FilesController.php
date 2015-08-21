<?php

require_once 'application/controllers/BaseController.php';

class FilesController extends BaseController
{

    public function searchAction()
    {
        $this->checkAccessAndRedirect(array('files/search'));

        $maxRecords = 500;
        $this->view->bread = $this->functions->T("menu_general") . "->" . $this->functions->T("menu_files_search");

        $this->view->addButton = "/files/add";
        $this->view->exportButton = true;
        $this->view->printButton = true;


        $agendaStates = $this->functions->getUserSetting("AGENDA_STATES");
        $this->view->agendaStates = $agendaStates;


        $rights = new Application_Model_UserRights();

        if ($rights->hasRights($this->auth->online_user_id, 'DELETE_FILE')) {
            $this->view->deleteRight = true;
        }


        if ($this->getParam("delete") && $rights->hasRights($this->auth->online_user_id, 'DELETE_FILE')) {
            $this->_delete($this->getParam("delete"));
            $this->view->deleted = true;
        }


        $form = new Application_Form_Files();
        $this->view->SearchBox = $form;


        if ($this->auth->online_rights != 5) {
            $this->view->showClient = true;
        }
        if ($this->auth->online_rights != 5 && $this->auth->online_rights != 6) {
            $this->view->showCollector = true;
        }

        $session = new Zend_Session_Namespace('FILES');

        $extraQuery = "";

        if (empty($session->orderby)) {
            $session->orderby = "FILE_NR";
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


        if ($this->getParam('agenda')) {
            $session->data['state_id'] = $this->getParam('agenda');
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
            if (!empty($session->data['closed_files']) and $session->data['extra_field'] != "DATE_CLOSED") {
                $closed_query = "and A.DATE_CLOSED is null AND A.STATE_ID != 40";
            } else {
                $closed_query = "";
            }
            if ($session->data['client'] != "") {
                $query_extra = " and A.CLIENT_NAME CONTAINING  '{$session->data['client']}'";
            }
            if ($session->agenda != "") {
                $query_extra = " and A.CLIENT_NAME CONTAINING  '{$session->data['client']}'";
            }
            if (!empty($session->data['debtor_name'])) {
                $query_extra .= " and A.DEBTOR_NAME CONTAINING '" . $session->data['debtor_name'] . "'";
            }
            if ($session->data['from_file_nr'] != "" and $session->data['to_file_nr'] != "")
                $query_extra .= " and A.FILE_NR >='{$session->data['from_file_nr']}'";
            if ($session->data['from_file_nr'] != "" and $session->data['to_file_nr'] == "")
                $query_extra .= " and A.FILE_NR = '{$session->data['from_file_nr']}'";
            if ($session->data['to_file_nr'] != "")
                $query_extra .= " and A.FILE_NR <='{$session->data['to_file_nr']}'";
            if ($session->data['client_reference'] != "")
                $query_extra .= " and A.REFERENCE CONTAINING '{$session->data['client_reference']}' ";
            if ($session->data['invoice'] != "") {
                $query_extra .= " and (SELECT COUNT(*) FROM FILES\$REFERENCES R WHERE REFERENCE CONTAINING '{$session->data['invoice']}' AND A.FILE_ID = R.FILE_ID) >=1 ";
            }
            if ($session->data['collector'] != "") {
                $query_extra .= " and A.COLLECTOR_ID = '{$session->data['collector']}' ";
            }
            if ($session->data['external_collector'] != "") {
                $query_extra .= " and B.EXTERNAL_COLLECTOR_ID = '{$session->data['external_collector']}' ";
            }
            if ($session->data['state_id'] != "") {
                $query_extra .= " and A.STATE_ID = '{$session->data['state_id']}' ";
            }
            if ($session->data['train_id'] != "") {
                $escTrainType = $this->db->escape($session->data['train_id']);
                $query_extra .= " and A.DEBTOR_ID IN (SELECT DEBTOR_ID FROM FILES\$DEBTORS D WHERE D.TRAIN_TYPE = '{$escTrainType}')";
            }
            if ($session->data['debtor'] != "") {
                $query_extra .= " and A.DEBTOR_NAME CONTAINING '{$session->data['debtor']}' ";
            }

            if (empty($session->data['closed_files'])) {
                $query_extra .= "and A.DATE_CLOSED is null AND A.STATE_ID != 40";
            }

            if ($query_extra == "") {
                $query_extra = "AND 1=1";
            }

            if ($data['extra_text'] != "") {

                if($data['extra_field'] === "DEBTOR_SCORE") {
                    $scorePart = $this->getDebtorScorePart($data['extra_compare'], $data['extra_text']);
                    if($scorePart) {
                        $query_extra .= $scorePart;
                    } else {
                        //TODO: handle incorrect data
                    }
                } else{
                    if (stripos($data['extra_field'], "DATE") !== false) {
                        $session->data['extra_text'] = $this->functions->date_dbformat($session->data['extra_text']);
                    }
                    if (is_numeric($this->functions->dbBedrag($session->data['extra_text']))) {
                        $session->data['extra_text'] = $this->functions->dbBedrag($session->data['extra_text']);
                    }

                    if (!isset($extra_compare_query)) {
                        $extra_compare_query = "";
                    }
                    $extra_compare_query .= " and A.{$session->data['extra_field']} {$session->data['extra_compare']} '{$session->data['extra_text']}' ";
                    $query_extra .= "$extra_compare_query";
                }

            }
        }

        $filesObj = new Application_Model_Files();
        $query_extra .= $filesObj->extraWhereClauseForUserRights($this->auth);

        $sql = "SELECT COUNT(*) AS COUNTER,SUM(A.TOTAL+A.INCASSOKOST) AS TOTAL, SUM(A.PAYABLE+A.INCASSOKOST) AS PAYABLE FROM FILES\$FILES_ALL_INFO A
                LEFT JOIN FILES\$FILES B ON A.FILE_ID = B.FILE_ID
                WHERE 1=1 {$query_extra}";
        $totals = $this->db->get_row($sql);
        $this->view->totals = $totals;

        $sql = "SELECT DISTINCT A.DATE_CLOSED,A.FILE_ID,A.CLIENT_NAME,A.CREATION_DATE,A.FILE_NR,A.STATE_CODE,A.REFERENCE,A.COLLECTOR_CODE,A.LAST_ACTION_DATE,A.AMOUNT,A.INTEREST,A.COSTS,(A.TOTAL+A.INCASSOKOST) AS TOTAL,
              (A.PAYABLE+A.INCASSOKOST) AS PAYABLE,A.PAYED_AMOUNT,A.PAYED_INTEREST,A.PAYED_COSTS,A.PAYED_UNKNOWN,A.PAYED_TOTAL,(A.SALDO+A.INCASSOKOST) AS SALDO,A.DEBTOR_NAME,A.DEBTOR_VAT_NR,A.DEBTOR_BIRTH_DAY,A.DEBTOR_ADDRESS,A.DEBTOR_ZIP_CODE,A.DEBTOR_CITY,
              A.DEBTOR_LANGUAGE_CODE,A.DATE_CLOSED,A.CLOSE_STATE_DESCRIPTION,
              (SELECT FIRST 1 SCORE FROM DEBTOR_SCORE DS WHERE DS.DEBTOR_ID = A.DEBTOR_ID ORDER BY TIME_STAMP DESC) AS DEBTOR_SCORE
              FROM FILES\$FILES_ALL_INFO A
              LEFT JOIN FILES\$FILES B ON A.FILE_ID = B.FILE_ID
              WHERE 1=1 {$query_extra} order by {$session->orderby} {$session->order}";

        if ($totals->COUNTER > $maxRecords) {
            $sql = str_replace("SELECT DISTINCT ", "SELECT FIRST {$maxRecords} DISTINCT ", $sql);
            $this->view->onlyFirst = $maxRecords;
        }
        $results = $this->db->get_results($sql);
        if (!empty($results)) {
            $this->export->sql = $sql;
            $fileList = array();
            foreach ($results as $row) {
                $fileList[] = array(
                    "FILE_ID" => $row->FILE_ID,
                    "FILE_NR" => $row->FILE_NR,
                    "DEBTOR_NAME" => $row->DEBTOR_NAME,
                );
            }
            $session->fileList = $fileList;
            $this->view->results = $results;
        } else {
            $this->export->sql = "";
            $this->view->exportButton = false;
        }

        $filesReferencesModel = new Application_Model_FilesReferences();
        $this->view->totalNotDue = $filesReferencesModel->getTotalNotDue();
        $this->view->totalPastDue = $filesReferencesModel->getTotalPastDue();
    }

    public function getDebtorScorePart($compare, $value){
        $operatorMapping = array('<=', '>=', '=');
        $operator = '=';
        if(in_array($compare, $operatorMapping)) {
            $operator = $compare;
        }
        return " AND (SELECT FIRST 1 SCORE FROM DEBTOR_SCORE DS WHERE DS.DEBTOR_ID = A.DEBTOR_ID ORDER BY TIME_STAMP DESC) {$operator} {$value}";
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

