<?php

require_once 'application/controllers/BaseClientController.php';

class ClientInvoiceParamsController extends BaseClientController {

    public function viewAction() {
        if ($this->auth->online_rights != 5) {
            $this->view->addButton = "/client-invoice-params/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        $conditionModel = new Application_Model_Conditions();
        $userRightsModel = new Application_Model_UserRights();

        $canDelete = $userRightsModel->getUserRightByColumn($this->auth->online_user_id, 'CLIENT_INVOICE_PARAMETERS');

        if ($this->getParam("delete") && $canDelete) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $conditions = $conditionModel->getConditions($this->clientId);

        $this->view->canDelete = $canDelete;
        $this->view->conditions = $conditions;
    }

    public function addAction()
    {
        $form = new Application_Form_ClientInvoiceParams();
        $conditionsModel = new Application_Model_Conditions();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $conditionsModel->add($add, $this->clientId);
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
        }
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Conditions();
        $Obj->delete($id);

        return true;
    }

}

