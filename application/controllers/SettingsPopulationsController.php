<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsPopulationsController extends BaseController
{

    public function viewAction() {

        $this->checkAccessAndRedirect(array('settings-populations/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-pupulations_view");

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-populations/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsPopulationModel = new Application_Model_Populations();

        $results = $settingsPopulationModel->getSettingPopulation();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-pupulations_view") .": " . $this->functions->T('new_c') ;

        $form = new Application_Form_Settings_Populations();
        $settingsModel = new Application_Model_Populations();
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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-pupulations_view") .": " . $this->functions->T('edit_c') ;

        $form = new Application_Form_Settings_Populations();
        $obj = new Application_Model_Populations();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "POPULATION_PLACE_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getSetting($this->getParam('id'));
            $data = array();
            $data['NAME'] = $row->NAME;
            $data['ADDRESS'] = $row->ADDRESS;
            $data['FAX'] = $row->FAX;
            $data['ZIP_CODE'] = $row->ZIP_CODE;
            $data['CITY'] = $row->CITY;
            $data['AMOUNT'] = number_format($row->AMOUNT, 2, ',', '.');
            $data['ACCOUNT_NO'] = $row->ACCOUNT_NO;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Populations();

        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

