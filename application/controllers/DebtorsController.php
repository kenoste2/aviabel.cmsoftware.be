<?php

require_once 'application/controllers/BaseController.php';

class DebtorsController extends BaseController {

    public function searchAction() {

        $obj = new Application_Model_Debtors();

        $this->view->bread = $this->functions->T("menu_general") . "->" . $this->functions->T("menu_debtors_search")  ;


        $maxRecords = 500;
        $this->view->exportButton = true;
        $this->view->printButton = true;


        $form = new Application_Form_Debtors();
        $this->view->SearchBox = $form;

        $session = new Zend_Session_Namespace('DEBTORS');
        
        
        if (empty($session->orderby)) {
            $session->orderby = "NAME";
        }
        if (empty($session->order)) {
            $session->order = "ASC";
        }


        if ($form->isValid($_POST)) {
            $data = $form->getValues();
            $session->data = $data;
        }
        if (!empty($session->data)) {
            $form->populate($session->data);
        }

        $query_extra = "";
        if (!empty($session->data)) {
            $query_extra = $obj->getDebtorsQuery($session->data);
        }

        $sql = "SELECT COUNT(*) AS COUNTER FROM FILES\$DEBTORS_ALL_INFO A WHERE 1=1 {$query_extra}";
        $totals = $this->db->get_row($sql);
        $this->view->totals = $totals;

        $sql = "select A.DEBTOR_ID,A.NAME,A.ADDRESS,A.ZIP_CODE,A.CITY,A.OPEN_FILES,A.VATNR from FILES\$DEBTORS_ALL_INFO A WHERE 1=1 $query_extra order by A.NAME";

        if ($totals->COUNTER > $maxRecords) {
            $sql = str_replace("SELECT ", "SELECT FIRST {$maxRecords} ", $sql);
            $this->view->onlyFirst = $maxRecords;
        }

        $results = $this->db->get_results($sql);
        if (!empty($results)) {
            $sql = "SELECT DEBTOR_ID,NAME FROM FILES\$DEBTORS_ALL_INFO A WHERE 1=1 {$query_extra} order by {$session->orderby} {$session->order}";
            $session->debtorList = $this->db->get_results($sql, ARRAY_A);
            
            
            $this->view->results = $results;
            $this->export->sql = $sql;
            $this->view->exportButton = true;
        } else {
            $this->export->sql = "";
            $this->view->exportButton = false;
        }
    }

}

