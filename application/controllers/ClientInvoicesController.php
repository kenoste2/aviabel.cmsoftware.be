<?php

require_once 'application/controllers/BaseClientController.php';

class ClientInvoicesController extends BaseClientController {

    public function viewAction() {
        $this->view->printButton = true;

        $invoicesModel = new Application_Model_Invoices();
        $invoices = $invoicesModel->getInvoicesByClient($this->clientId);

        $this->view->invoices = $invoices;
    }

}

