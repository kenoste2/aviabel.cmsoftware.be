<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsTemplatesController extends BaseController
{

    public function viewAction() {

        $this->checkAccessAndRedirect(array('settings-templates/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-templates_view") . ": " . $this->functions->T('new_c');
        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-templates/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $templatesModel = new Application_Model_Templates();

        $results = $templatesModel->getTemplates();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-templates_view") . ": " . $this->functions->T('new_c');

        $form = new Application_Form_Settings_Templates();
        $settingsModel = new Application_Model_Templates();
        $filesAllInfoModel = new Application_Model_FilesAllInfo();
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
                }
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
        }
        $form->populate($data);

        $this->view->filesAllInfo = $filesAllInfoModel->getAllInfo();
        $this->view->form = $form;
    }

    public function editAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-templates_view") . ": " . $this->functions->T('edit_c');

        $form = new Application_Form_Settings_Templates();
        $obj = new Application_Model_Templates();
        $filesAllInfoModel = new Application_Model_FilesAllInfo();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $exists = $obj->getByCode($data['CODE']);
                if (!empty($exists) && $exists['TEMPLATE_ID'] != $this->getParam('id')) {
                    $this->view->codeExistsError = true;
                } else {
                    $obj->save($update, "TEMPLATE_ID = {$this->getParam('id')}");
                    $this->view->formSaved = true;
                }

            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getTemplate($this->getParam('id'));
            $data = array();
            $data['CODE'] = $row->CODE;
            $data['DESCRIPTION'] = $row->DESCRIPTION;
            $data['ACTION_ID'] = $row->ACTION_ID;
            $data['TEXT_NL'] = $row->TEXT_NL;
            $data['TEXT_FR'] = $row->TEXT_FR;
            $data['TEXT_EN'] = $row->TEXT_EN;
            $data['TEXT_DE'] = $row->TEXT_DE;
            $data['TEXT_SMS_NL'] = $row->TEXT_SMS_NL;
            $data['TEXT_SMS_FR'] = $row->TEXT_SMS_FR;
            $data['TEXT_SMS_EN'] = $row->TEXT_SMS_EN;
            $data['TEXT_SMS_DE'] = $row->TEXT_SMS_DE;
            $data['TEMPLATE_FOR'] = $row->TEMPLATE_FOR;
            $data['TEMPLATE_MODULES'] = explode(',', $row->TEMPLATE_MODULES);
        }
        // Populating form
        $form->populate($data);

        $this->view->filesAllInfo = $filesAllInfoModel->getAllInfo();
        $this->view->form = $form;
    }

    private function delete($id)
    {
        $Obj = new Application_Model_Templates();

        if ($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

