<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsFilecostsController extends BaseController
{

    public function viewAction()
    {
        $this->checkAccessAndRedirect(array('settings-filecosts/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-filecosts_view");

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-filecosts/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsFilecostsModel = new Application_Model_Filecosts();

        $results = $settingsFilecostsModel->getSettingFilecosts();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-filecosts_view") .": " . $this->functions->T('new_c') ;
        $this->view->nav = 'settings-filecosts/view';
        $form = new Application_Form_Settings_Filecosts();
        $settingsModel = new Application_Model_Filecosts();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $settingsModel->add($add);
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

    public function editAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-filecosts_view") .": " . $this->functions->T('edit_c') ;
        $this->view->nav = 'settings-filecosts/view';
        $form = new Application_Form_Settings_Filecosts();
        $obj = new Application_Model_Filecosts();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "COST_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getSetting($this->getParam('id'));
            $data = array();
            $data['CODE'] = $row->CODE;
            $data['DESCRIPTION'] = $row->DESCRIPTION;
            $data['AMOUNT'] = number_format($row->AMOUNT, 2, ',', '.');
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Filecosts();

        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

