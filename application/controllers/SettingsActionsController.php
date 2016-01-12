<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsActionsController extends BaseController
{

    public function viewAction()
    {
        $this->checkAccessAndRedirect(array('settings-actions/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-actions_view");

        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-actions/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $settingsActionsModel = new Application_Model_Actions();

        $results = $settingsActionsModel->getSettingActions();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-actions_view") . ": " . $this->functions->T('new_c');
        $this->view->nav = 'settings-actions/view';
        $form = new Application_Form_Settings_Actions();
        $actionsModel = new Application_Model_Actions();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $exists = $actionsModel->getActionByCode($data['CODE']);
                if (!empty($exists)) {
                    $this->view->codeExistsError = true;
                } else {
                    $actionsModel->add($add);
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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-actions_view") . ": " . $this->functions->T('edit_c');
        $this->view->nav = 'settings-actions/view';
        $form = new Application_Form_Settings_Actions();
        $obj = new Application_Model_Actions();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $exists = $obj->getActionByCode($data['CODE']);
                if (!empty($exists) && $exists['ACTION_ID'] != $this->getParam('id') ) {
                    $this->view->codeExistsError = true;
                } else {
                    $obj->save($update, "ACTION_ID = {$this->getParam('id')}");
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
            $data['FILE_STATE_ID'] = $row->FILE_STATE_ID;
            $data['VISIBLE'] = $row->VISIBLE;
            $data['CONFIRMATION_NEEDED'] = $row->CONFIRMATION_NEEDED;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id)
    {
        $Obj = new Application_Model_Actions();
        $Obj->delete($id);
        return true;
    }

}

