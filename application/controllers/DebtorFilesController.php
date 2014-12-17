<?php

require_once 'application/controllers/BaseDebtorController.php';

class DebtorFilesController extends BaseDebtorController {

    public function viewAction() {
        $obj = new Application_Model_Debtors();
        $this->view->printButton = true;
        $this->view->results = $obj->getAllFiles($this->debtorId);

        $sessionFiles = new Zend_Session_Namespace("FILES");
        $sql = "SELECT FILE_ID,FILE_NR,DEBTOR_NAME FROM FILES\$FILES_ALL_INFO A WHERE DEBTOR_ID = {$this->debtorId}";
        $sessionFiles->fileList = $this->db->get_results($sql, ARRAY_A);
    }

}

