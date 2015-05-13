<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsUsersController extends BaseController
{

    public function viewAction()
    {
        $this->checkAccessAndRedirect(array('settings-users/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-users_view");
        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/settings-users/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $userModel = new Application_Model_Users();

        $results = $userModel->getAllUsers();

        $this->view->results = $results;
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-users_view") . ": " . $this->functions->T('new_c');
        $form = new Application_Form_Settings_Users();
        $userModel = new Application_Model_Users();
        $data = array();
        if ($this->getRequest()->isPost()) {
            $request = $this->getRequest()->getPost();
            if ($request['RIGHTS'] == '5') {
                $form->CLIENT_ID->setRequired(true);
            }
            if ($request['RIGHTS'] == '6') {
                $form->COLLECTOR_ID->setRequired(true);
            }

            if ($form->isValid($request)) {
                $data = $add = $form->getValues();
                $exists = $userModel->getByCode($data['CODE']);
                if (!empty($exists)) {
                    $this->view->codeExistsError = true;
                } else {
                    $userModel->createUser($add);
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
        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("menu_settings-users_view") . ": " . $this->functions->T('edit_c');
        $form = new Application_Form_Settings_Users();
        $obj = new Application_Model_Users();
        $userRightsModel = new Application_Model_UserRights();

        $data = array();
        if ($this->getRequest()->isPost()) {

            $request = $this->getRequest()->getPost();
            if ($request['RIGHTS'] == '5') {
                $form->CLIENT_ID->setRequired(true);
            }
            if ($request['RIGHTS'] == '6') {
                $form->COLLECTOR_ID->setRequired(true);
            }
            if ($form->isValid($request)) {
                $data = $update = $form->getValues();
                $exists = $obj->getByCode($data['CODE']);
                if (!empty($exists) && $exists['USER_ID'] != $this->getParam('id')) {
                    $this->view->codeExistsError = true;
                } else {
                    $obj->save($update, $this->getParam('id'));
                    $this->view->formSaved = true;
                }
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $user_id = $this->getParam('id');
            $row = $obj->getUser($user_id);
            $data = array();
            $data['CODE'] = $row->CODE;
            $data['NAME'] = $row->NAME;
            $data['E_MAIL'] = $row->E_MAIL;
            $data['RIGHTS'] = $row->RIGHTS;
            $data['CLIENT_ID'] = $row->CLIENT_ID;
            $data['COLLECTOR_ID'] = $row->COLLECTOR_ID;
            $data['USER_RIGHTS'] = $userRightsModel->getYesFields($userRightsModel->getUserRights($user_id));
        }
        // Populating form
        $form->populate($data);
        $this->view->form = $form;
    }

    private function delete($id)
    {
        $Obj = new Application_Model_Users();

        if ($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

