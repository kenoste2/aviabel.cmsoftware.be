<?php

require_once 'application/controllers/BaseClientController.php';

class ClientContactsController extends BaseClientController {

    public function viewAction()
    {
        if ($this->auth->online_rights != 5) {
            $this->view->addButton = "/client-contacts/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        $clientContactsModel = new Application_Model_ClientsContacts();

        $results = $clientContactsModel->getClientContacts($this->clientId);

        $this->view->results = $results;
    }

    public function addAction()
    {
        $form = new Application_Form_ClientContact();
        $contactModel = new Application_Model_ClientsContacts();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $add['CLIENT_ID'] = $this->clientId;
                $contactModel->add($add);
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
        $form = new Application_Form_ClientContact();
        $obj = new Application_Model_ClientsContacts();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, "CONTACT_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getContact($this->getParam('id'));
            $data = array();
            $data['FUNCTION_DESCRIPTION'] = $row->FUNCTION_DESCRIPTION;
            $data['NAME'] = $row->NAME;
            $data['EMAIL'] = $row->EMAIL;
            $data['TEL'] = $row->TEL;
            $data['FAX'] = $row->FAX;
            $data['LANGUAGE_CODE_ID'] = $row->LANGUAGE_CODE_ID;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_ClientsContacts();
        $Obj->delete($id);
    }

}

