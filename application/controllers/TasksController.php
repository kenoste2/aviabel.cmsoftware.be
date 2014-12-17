<?php

require_once 'application/controllers/BaseController.php';

class TasksController extends BaseController {

    public function searchAction()
    {
        $this->view->bread = $this->functions->T("menu_general") . "->" . $this->functions->T("menu_tasks_search")  ;
        if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/tasks/add/index/" . $this->getParam("index");
        }
        $this->view->printButton = true;

        if ($this->getParam("delete")) {
            $isDeleted = $this->delete($this->getParam("delete"));
            $this->view->deleted = $isDeleted;
        }

        $searchTaskForm = new Application_Form_SearchTasks();
        $taskModel = new Application_Model_Tasks();

        if($this->getRequest()->isPost()){
            $searchTaskForm->isValid($this->getRequest()->getPost());
        }

        $data = $searchTaskForm->getValues();

        if ($this->auth->online_rights == 5)
        {
            $data['client_id'] = $this->auth->online_client_id;
        }

        $tasks = $taskModel->getTasks($data);

        $this->view->tasks = count($tasks) ? $tasks : array();
        $this->view->searchTaskForm = $searchTaskForm;
        $this->view->canChange = $this->auth->online_rights != 5 ? true : false;
        $this->view->exportButton = count($tasks) ? true : false;
        $this->export->sql = count($tasks) ? $taskModel->getSql() : '';
    }

    public function addAction()
    {
        $this->view->bread = $this->functions->T("menu_general") . "->" . $this->functions->T("menu_tasks_search") .": " . $this->functions->T('new_c') ;
        $form = $this->cleanupForm('add', new Application_Form_Tasks());
        $taskModel = new Application_Model_Tasks();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $taskModel->add($add);
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
        $this->view->bread = $this->functions->T("menu_general") . "->" . $this->functions->T("menu_tasks_search") .": " . $this->functions->T('edit_c') ;
        $form = $this->cleanupForm('edit', new Application_Form_Tasks());
        $obj = new Application_Model_Tasks();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $update = $form->getValues();
                $obj->save($update, $this->getParam('id'));
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $row = $obj->getTask($this->getParam('id'));
            $data = array();
            $data['TODO_TYPE'] = $row->TODO_TYPE;
            $data['REMARK'] = $row->REMARK;
            $data['ASSIGNED_TO'] = $row->ASSIGNED_TO;
            $data['DONE'] = $row->DONE;
        }
        // Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    protected function cleanupForm($type, $form)
    {
        switch ($type) {
            case 'add':
                $form->removeElement('DONE');
                break;
            case 'edit':
                $form->removeElement('FILE_NR');
                $form->removeElement('DEBTOR_NAME');
                break;
        }

        return $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_Tasks();
        if($Obj->checkIsDeletable($id)) {
            $Obj->delete($id);
            return true;
        }

        return false;
    }

}

