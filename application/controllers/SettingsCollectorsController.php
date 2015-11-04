<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsCollectorsController extends BaseController
{

    public function viewAction()
    {
        $this->checkAccessAndRedirect(array('settings-collectors/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-collectors_view");

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-collectors/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $collectorsModel = new Application_Model_Collectors();

        $results = $collectorsModel->getCollectors();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-collectors_view") .": " . $this->functions->T('new_c') ;
        $form = new Application_Form_Settings_Collectors();
        $collectorsModel = new Application_Model_Collectors();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();


                $collectorsModel->add($add);
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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-collectors_view") .": " . $this->functions->T('edit_c') ;
        $form = new Application_Form_Settings_Collectors();
        $obj = new Application_Model_Collectors();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "COLLECTOR_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getCollector($this->getParam('id'));
            $data = array();
            $data['CODE'] = $row->CODE;
            $data['NAME'] = $row->NAME;
            $data['ADDRESS'] = $row->ADDRESS;
            $data['ZIP_CODE'] = $row->ZIP_CODE;
            $data['CITY'] = $row->CITY;
            $data['COUNTRY_ID'] = $row->COUNTRY_ID;
            $data['LANGUAGE_ID'] = $row->LANGUAGE_ID;
            $data['EMAIL'] = $row->EMAIL;
            $data['TELEPHONE'] = $row->TELEPHONE;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Collectors();

        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

