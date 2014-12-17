<?php

require_once 'application/controllers/BaseClientController.php';

class ClientCostsController extends BaseClientController {

    public function viewAction() {
        $this->view->printButton = true;

        $costsModel = new Application_Model_Filecosts();
        $costForm = new Application_Form_ClientCosts();

        if ($this->getRequest()->isPost()) {
            $costForm->isValid($this->getRequest()->getPost());
        }
        $data = $costForm->getValues();

        $costs = $costsModel->getFileCosts($this->clientId, $data);

        $this->view->costs = $costs;
        $this->view->costForm = $costForm;
        $this->view->exportButton = count($costs) ? true : false;
        $this->export->sql = count($costs) ? $costsModel->getSql() : '';
    }

}

