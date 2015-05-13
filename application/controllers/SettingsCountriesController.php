<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsCountriesController extends BaseController
{

    public function viewAction() {

        $this->checkAccessAndRedirect(array('settings-countries/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-countries_view");
        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-countries/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsCountriesModel = new Application_Model_Countries();

        $results = $settingsCountriesModel->getAllCountries();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-countries_view") .": " . $this->functions->T('new_c') ;

        $form = new Application_Form_Settings_Country();
        $settingsModel = new Application_Model_Countries();
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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-countries_view") .": " . $this->functions->T('edit_c') ;

        $form = new Application_Form_Settings_Country();
        $obj = new Application_Model_Countries();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "COUNTRY_ID = {$this->getParam('id')}");
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
            $data['DELTA'] = $row->DELTA;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Countries();

        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

