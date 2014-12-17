<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsTrajectController extends BaseController
{

    public function viewAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-traject_view");
        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-traject/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $trajectModel = new Application_Model_Train();

        $results = $trajectModel->getTrains();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-traject_view") .": " . $this->functions->T('new_c') ;
        $form = new Application_Form_Settings_Trajects();
        $settingsModel = new Application_Model_Train();

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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-traject_view") .": " . $this->functions->T('edit_c') ;
        $form = new Application_Form_Settings_Trajects();
        $obj = new Application_Model_Train();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getTrain($this->getParam('id'));
            $data = array();
            $data['CODE'] = $row->CODE;
            $data['DESCRIPTION'] = $row->DESCRIPTION;
            $data['ACTIEF'] = $row->ACTIEF;
            $data['TRAIN_TYPE'] = $row->TRAIN_TYPE;
            $data['OPEN_FILES'] = $row->OPEN_FILES;
            $data['DAYS'] = $row->DAYS;
            $data['PAYMENTS'] = $row->PAYMENTS;
            $data['OTHER_ACTIONS'] = $row->OTHER_ACTIONS;
            $data['EXTRA_RULES'] = $row->EXTRA_RULES;
            $data['SETACTION'] = $row->SETACTION;
            $data['ORDER_CYCLE'] = $row->ORDER_CYCLE;
            $data['STATE_ID'] = $row->STATE_ID;
            $data['TEMPLATE_ID'] = $row->TEMPLATE_ID;
            $data['ACTIONBOX'] = explode(',', $row->ACTIONBOX);
            $data['STATEBOX'] = explode(',', $row->STATEBOX);
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Train();

        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

