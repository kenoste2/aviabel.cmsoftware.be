<?php

class Application_Form_FileTodo extends Zend_Form {

    public function init($fileId = false) {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $todoObj = new Application_Model_FilesTodos();

        $this->addElement('select', 'TODO_TYPE', array(
            'label' => $functions->T('todo_type_c'),
            'required' => false,
            'MultiOptions' => $todoObj->getTodoTypes(),
        ));

        $this->addElement('select', 'ASSIGNED_TO', array(
            'label' => $functions->T('assigned_to_c'),
            'required' => false,
            'MultiOptions' => $todoObj->getTodoUsers(),
        ));




        $confirm = array(1 => $functions->T('yes_c'), 0 => $functions->T('no_c'));
        $this->addElement('radio', 'DONE', array(
            'label' => $functions->T('done_c'),
            'required' => false,
            'MultiOptions' => $confirm,
        ));
        $this->addElement('textarea', 'REMARK', array('label' => $functions->T('remark_c'), 'required' => true, 'rows' => 5, 'cols' => 50));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

