<?php

class Application_Form_Tasks extends Zend_Form
{

    public function init()
    {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'FILE_NR', array('label' => $functions->T('Invoice_Nr_c'), 'size'=> 15, 'required' => true));
        $this->addElement('text', 'DEBTOR_NAME', array('label' => $functions->T('debtor_c'), 'size'=> 15, 'required' => true, 'attribs' => array('readonly'=>true)));
        $this->addElement('select', 'TODO_TYPE', array('label' => $functions->T('type_c'), 'required' => false, 'MultiOptions' => $this->getTodoTypes($functions)));
        $this->addElement('textarea', 'REMARK', array('label' => $functions->T('description_c'), 'required' => false, 'cols' => 75, 'rows' => 10));
        $this->addElement('select', 'ASSIGNED_TO', array('label' => $functions->T('assigned_to_c'), 'required' => true, 'MultiOptions' => $this->getAssignedTos($functions)));
        $this->addElement('radio', 'DONE', array('label' => $functions->T('done_c'), 'required' => true, 'separator' => '', 'MultiOptions' => $this->getCompleted($functions)));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

    protected function getTodoTypes(Application_Model_CommonFunctions $functions)
    {
        $types = explode("\n", $functions->T('setting_file_todo_types'));
        $todo_types[''] = '';
        foreach($types as $type )
        {
            list($code,$name) = explode(",",$type);
            $todo_types[$code] = $name;
        }

        return $todo_types;
    }

    protected function getCompleted(Application_Model_CommonFunctions $functions)
    {
        return array(
            '1' => $functions->T('yes_c'),
            '0' => $functions->T('no_c'),
        );
    }

    protected function getAssignedTos(Application_Model_CommonFunctions $functions)
    {
        $types = explode("\n", $functions->T('todo_users_list'));
        $users_list[''] = '';
        foreach($types as $type )
        {
            list($code,$name) = explode(",",$type);
            $users_list[$code] = $name;
        }

        return $users_list;
    }

}

