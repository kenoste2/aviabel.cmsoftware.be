<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsTextsController extends BaseController
{

    public function viewAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-texts_view");
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $textsModel = new Application_Model_Texts();

        $search = $this->getRequest()->getParam('zoeken', '');
        $results = $textsModel->getTexts($search);

        $this->view->search = $search;
        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-texts_view") .": " . $this->functions->T('new_c') ;
        $form = new Application_Form_Settings_Texts();
        $settingsModel = new Application_Model_Texts();
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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-texts_view") .": " . $this->functions->T('edit_c') ;
        $form = new Application_Form_Settings_Texts();
        $obj = new Application_Model_Texts();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "TEKSTEN_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getText($this->getParam('id'));
            $data = array();
            $data['CODE'] = $row->CODE;
            $data['NAV'] = $row->NAV;
            $data['NL'] = $this->db->escape($row->NL);
            $data['FR'] = $this->db->escape($row->FR);
            $data['EN'] = $this->db->escape($row->EN);
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Texts();

        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

