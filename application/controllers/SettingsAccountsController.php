<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsAccountsController extends BaseController
{

    public function viewAction() {

        $this->checkAccessAndRedirect(array('settings-accounts/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-accounts_view")  ;

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-accounts/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsAccountsModel = new Application_Model_Accounts();

        $results = $settingsAccountsModel->getSettingAccounts();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-accounts_view") .": " . $this->functions->T('new_c') ;
        $this->view->nav = 'settings-accounts/view';
        $form = new Application_Form_Settings_Accounts();
        $settingsModel = new Application_Model_Accounts();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $result = $settingsModel->add($add);

                if ($result === false) {
                    $form->getElement('CODE')->addError('Code already exists in active or non-active account');
                    $form->markAsError();

                    $this->view->codeExists = true;
                } else {
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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-accounts_view") .": " . $this->functions->T('edit_c') ;
        $this->view->nav = 'settings-accounts/view';
        $form = new Application_Form_Settings_Accounts();
        $obj = new Application_Model_Accounts();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $result = $obj->save($update, $this->getParam('id'));
                if ($result === false) {
                    $form->getElement('CODE')->addError('Code already exists in active or non-active account');
                    $form->markAsError();
                    $this->view->codeExists = true;
                } else {
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
            $data['ACCOUNT_NR'] = $row->ACCOUNT_NR;
            $data['IN_HOUSE'] = $row->IN_HOUSE;
            $data['VALUTA'] = $row->VALUTA;
            $data['BIC'] = $row->BIC;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Accounts();

        if ($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

