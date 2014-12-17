<?php

require_once 'application/controllers/BaseFileController.php';

class FileTodosController extends BaseFileController {

    public function viewAction() {

        if ($this->hasAccess('addTasks') ) {
            $this->view->addButton = "/file-todos/add/index/" . $this->getParam("index");
        }
        if ($this->hasAccess('manageTasks') ) {
            $this->view->manageTasks = true;
        }
        $this->view->printButton = true;




        if ($this->getParam("delete")) {
            $obj = new Application_Model_FilesTodos();
            $row = $obj->getTodoById($this->getParam("delete"));
            if ($this->hasAccess('manageTasks') or $row->CREATION_USER == $this->auth->online_user) {
                $this->delete($this->getParam("delete"));
                $this->view->deleted = true;
            }
        }

        $sql = "select * from TODOS where FILE_ID='{$this->fileId}' order by TODO_ID DESC";
        $this->view->results = $this->db->get_results($sql);
        $this->view->online_user = $this->auth->online_user;
    }
  
    public function addAction() {

        if (!$this->hasAccess('addTasks') ) {
            $this->_redirect('/error/noaccess');
            return;
        }

            $form = new Application_Form_FileTodo();
        $obj = new Application_Model_FilesTodos();
        $form->removeElement('DONE');
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

        $form = new Application_Form_FileTodo();
        $obj = new Application_Model_FilesTodos();


        $row = $obj->getTodoById($this->getParam('id'));

        if (!$this->hasAccess('manageTasks') && $this->auth->online_user != $row->CREATION_USER ) {
            $this->_redirect('/error/noaccess');
            return;
        }

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $obj->save($update, "TODO_ID = {$this->getParam('id')}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
            $data['TODO_TYPE'] = $row->TODO_TYPE;
            $data['REMARK'] = $row->REMARK;
            $data['DONE'] = $row->DONE;
            $data['ASSIGNED_TO'] = $row->ASSIGNED_TO;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_FilesTodos();
        $Obj->delete($id);
    }


}

