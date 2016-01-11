<?php

require_once 'application/controllers/BaseController.php';

class SettingsAllowedMailsController extends BaseController
{

    public function viewAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-allowed-mails_view");

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-allowed-mails/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsModel = new Application_Model_AllowedMails();

        $results = $settingsModel->getSettingsAllowedMails();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-allowed-mails_view") . ": " . $this->functions->T('new_c');
        $form = new Application_Form_Settings_AllowedMails();
        $settingsModel = new Application_Model_AllowedMails();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $exists = $settingsModel->emailExists($data['EMAIL']);
                if (!empty($exists)) {
                    $this->view->codeExistsError = true;
                } else {
                    $settingsModel->add($add);
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

