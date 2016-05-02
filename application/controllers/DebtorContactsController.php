<?php

require_once 'application/controllers/BaseDebtorController.php';

class DebtorContactsController extends BaseDebtorController {

    public function viewAction()
    {
        if ($this->auth->online_rights != 5 && $this->auth->online_rights != 9 ) {
            $this->view->addButton = "/debtor-contacts/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        $debtorContactsModel = new Application_Model_DebtorsContacts();

        $results = $debtorContactsModel->getDebtorContacts($this->debtorId);

        $this->view->results = $results;
    }

    public function addAction()
    {
        $form = new Application_Form_DebtorContact();
        $contactModel = new Application_Model_DebtorsContacts();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $add['DEBTOR_ID'] = $this->debtorId;
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
        $form = new Application_Form_DebtorContact();
        $obj = new Application_Model_DebtorsContacts();

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
            $data['ADDRESS'] = $row->ADDRESS;
            $data['TEL'] = $row->TEL;
            $data['FAX'] = $row->FAX;
            $data['LANGUAGE_CODE_ID'] = $row->LANGUAGE_CODE_ID;
        }
        // Populating form
        $form->populate($data);

        if ($this->auth->online_rights == 9) {
            $form->removeElement('submit');
        }


        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_DebtorsContacts();
        $Obj->delete($id);
    }

}

