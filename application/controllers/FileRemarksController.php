<?php

require_once 'application/controllers/BaseFileController.php';

class FileRemarksController extends BaseFileController {

    public function viewAction() {

        if ($this->hasAccess('addRemarks') ) {
            $this->view->addButton = "/file-remarks/add/fileId/" . $this->fileId;
        }
        if ($this->hasAccess('manageRemarks') ) {
            $this->view->manageRemarks = true;
        }


        $this->view->printButton = true;

        if ($this->getParam("delete") && $this->hasAccess('manageRemarks')) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        if ($this->auth->online_rights == 5) {
            $selectionField = "REMARK_CLIENT AS REMARK";
        } else {
            $selectionField = "REMARK";
        }
        $sql = "select REMARK_TYPE,REMARK_ID,$selectionField,CREATION_DATE,CREATION_USER from FILES\$REMARKS where FILE_ID='{$this->fileId}' order by REMARK_ID DESC";
        $this->view->results = $this->db->get_results($sql);

        $this->view->online_user = $this->auth->online_user;
    }

    public function addAction() {

        if (!$this->hasAccess('addRemarks') ) {
            $this->_redirect('error/noaccess');
            return;
        }

        $form = new Application_Form_FileAddRemark();
        $obj = new Application_Model_FilesRemarks();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $update['FILE_ID'] = $this->fileId;
                $obj->add($update);
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

    public function editAction() {

        if (!$this->hasAccess('manageRemarks') ) {
            $this->_redirect('error/noaccess');
            return;
        }

        $form = new Application_Form_FileAddRemark();
        $obj = new Application_Model_FilesRemarks();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $obj->save($update, "REMARK_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $this->db->get_row("SELECT * FROM FILES\$REMARKS WHERE REMARK_ID = {$this->getParam('id')}");
            $data = array();
            $data['REMARK'] = $row->REMARK;
            $data['REMARK_CLIENT'] = $row->REMARK_CLIENT;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_FilesRemarks();
        $Obj->delete($id);
    }

}

