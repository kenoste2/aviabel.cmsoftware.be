<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsZipcodesController extends BaseController
{

    public function viewAction() {

        $this->checkAccessAndRedirect(array('settings-zipcodes/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-zipcodes_view");
        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-zipcodes/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsZipcodesModel = new Application_Model_ZipCodes();

        $results = $settingsZipcodesModel->getZipcodes();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-zipcodes_view") .": " . $this->functions->T('new_c') ;
        $form = new Application_Form_Settings_Zipcodes();
        $settingsModel = new Application_Model_ZipCodes();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();

                if (empty($add['COUNTRY_ID']) && !empty($add['NEW_COUNTRY_CODE'])) {
                    $add['COUNTRY_ID'] = $this->addNewCountry($add);
                }

                $settingsModel->create($add);
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

    protected function addNewCountry($data)
    {
        $countryModel = new Application_Model_Countries();

        $countryData = array(
            'CODE' => $data['NEW_COUNTRY_CODE'],
            'DESCRIPTION' => $data['NEW_COUNTRY_DESCRIPTION'],
        );

        $countryId = $countryModel->add($countryData);
        return $countryId;
    }

    public function editAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-zipcodes_view") .": " . $this->functions->T('edit_c') ;
        $form = new Application_Form_Settings_Zipcodes();
        $obj = new Application_Model_ZipCodes();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "ZIP_CODE_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getSetting($this->getParam('id'));
            $data = array();
            $data['ZIP_CODE'] = $row->CODE;
            $data['CITY_DUTCH'] = $row->CITY_DUTCH;
            $data['CITY_FRENCH'] = $row->CITY_FRENCH;
            $data['CITY_ENGLISH'] = $row->CITY_ENGLISH;
            $data['COUNTRY_ID'] = $row->COUNTRY_ID;
            $data['POPULATION_PLACE_ID'] = $row->POPULATION_PLACE_ID;
            $data['COLLECTOR_ID'] = $row->COLLECTOR_ID;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_ZipCodes();

        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

