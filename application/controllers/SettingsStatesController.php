<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsStatesController extends BaseController
{

    public function viewAction() {

        $this->checkAccessAndRedirect(array('settings-states/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-states_view");

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-states/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsStatesModel = new Application_Model_States();

        $results = $settingsStatesModel->getSettingStates();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-states_view") . ": " . $this->functions->T('new_c');
        $form = new Application_Form_Settings_States();
        $settingsModel = new Application_Model_States();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $exists = $settingsModel->getByCode($data['CODE']);
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

    public function editAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-states_view") . ": " . $this->functions->T('edit_c');
        $form = new Application_Form_Settings_States();
        $obj = new Application_Model_States();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $exists = $obj->getByCode($data['CODE']);
                if (!empty($exists) && $exists['STATE_ID'] != $this->getParam('id')) {
                    $this->view->codeExistsError = true;
                } else {
                    $obj->save($update, "STATE_ID = {$this->getParam('id')}");
                    $this->view->formSaved = true;
                }
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getSetting($this->getParam('id'));
            $data = array();
            $data['CODE'] = $row->CODE;
            $data['DESCRIPTION'] = $row->DESCRIPTION;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id)
    {
        $Obj = new Application_Model_States();

        if ($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

