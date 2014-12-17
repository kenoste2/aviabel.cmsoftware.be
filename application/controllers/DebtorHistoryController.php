<?php

require_once 'application/controllers/BaseDebtorController.php';

class DebtorHistoryController extends BaseDebtorController {

    public function viewAction() {
        $obj = new Application_Model_Debtors();
        $this->view->printButton = true;
        $this->view->results = $obj->getHistory($this->debtorId);
    }

}

