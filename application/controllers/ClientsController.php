<?php

require_once 'application/controllers/BaseController.php';

class ClientsController extends BaseController {

    public function searchAction() {

        $this->checkAccessAndRedirect(array('clients/search'));

        $obj = new Application_Model_Clients();
        $this->view->addButton = "/client-add/add/clientId/1";
        $this->view->bread = $this->functions->T("menu_general") . "->" . $this->functions->T("menu_clients_search")  ;

        $maxRecords = 1000;
        $this->view->printButton = true;

        $form = new Application_Form_SearchClients();
        $this->view->SearchBox = $form;

        if ($form->isValid($_POST)) {
            $data = $form->getValues();
        }

        if ($this->getParam('hide')) {
            $this->hide($this->getParam('hide'));
        }

        $query_extra = $obj->getClientsQuery(!empty($data) ? $data : '');
        list($results, $totals, $onlyFirst, $sql) = $obj->getAllClients($query_extra, $maxRecords);

        $this->view->totals = $totals;

        if (!empty($results)) {
            $this->view->results = $results;
            $this->view->onlyFirst = $onlyFirst;
            $this->view->exportButton = true;
            $this->export->sql = $sql;
        } else {
            $this->export->sql = "";
            $this->view->exportButton = false;
        }
    }

    private function hide($id)
    {
        $Obj = new Application_Model_Clients();
        $Obj->hide($id);
    }


}

