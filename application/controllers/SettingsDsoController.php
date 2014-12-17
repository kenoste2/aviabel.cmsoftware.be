<?php

require_once 'application/controllers/BaseController.php';

class SettingsDsoController extends BaseController
{

    public function viewAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-dso_view");

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-dso/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsDsoModel = new Application_Model_Dso();

        $results = $settingsDsoModel->getSettingDso();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-dso_view") . ": " . $this->functions->T('new_c');
        $form = new Application_Form_Settings_Dso();
        $settingsModel = new Application_Model_Dso();
        $clientModel = new Application_Model_Clients();

        list($clients) = $clientModel->getAllClients();

        if ($this->isClient()) {
            $clientId = $this->auth->online_client_id;
        } else {
            $clientId = $this->getRequest()->getParam('CLIENT_ID', $this->getSelectedClientId($clients));
        }

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $exists = $settingsModel->getByYearMonth($data['DSO_YEAR'],$data['DSO_MONTH']);
                if (!empty($exists)) {
                    $this->view->codeExistsError = true;
                } else {
                    $settingsModel->add($add, $clientId);
                    $this->view->formSaved = true;
                }
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
        }
        $form->populate($data);

        $this->view->clients = $clients;
        $this->view->clientId = $clientId;
        $this->view->isClient = $this->isClient();
        $this->view->form = $form;
    }

    private function delete($id)
    {
        $Obj = new Application_Model_Dso();

        $Obj->delete($id);
        return true;

        return false;
    }

    protected function getSelectedClientId(array $clients)
    {
        if ($this->isClient()) {
            return $this->auth->online_user_id;
        }

        $firstClient = reset($clients);
        return $firstClient->CLIENT_ID;
    }

    protected function isClient()
    {
        if ($this->auth->online_rights == 5) {
            return true;
        }

        return false;
    }
}

