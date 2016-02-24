<?php

class Application_Form_SearchTasks extends Zend_Form
{

    public function init()
    {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'STARTDATE', array('label' => $functions->T('aanmaakdatum_c'), 'size' => 20));
        $this->addElement('text', 'ENDDATE', array('label' => $functions->T('tot_c'), 'size' => 20));
        $this->addElement('radio', 'COMPLETED', array('label' => $functions->T('done_c'), 'separator' => '', 'MultiOptions' => $this->getCompleted($functions)));
        $this->addElement('select', 'TODOS_TYPE', array('label' => $functions->T('type_c'), 'MultiOptions' => $this->getTodoTypes($functions)));
        $this->addElement('select', 'SELECT_ASSIGNED_TO', array('label' => $functions->T('assigned_to_c'), 'MultiOptions' => $this->getAssignedTos($functions)));

        $this->addElement('hidden', 'search_payment', array('value' => 1));


        $this->addDisplayGroup(array('ENDDATE'), 'group2');
        $this->getDisplayGroup('group2')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:right;'))
        ));

        $this->addDisplayGroup(array('STARTDATE', 'COMPLETED', 'TODOS_TYPE', 'SELECT_ASSIGNED_TO'), 'group1');
        $this->getDisplayGroup('group1')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;'))
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));

        $this->getElement('STARTDATE')->setValue(date('01/m/Y'));
        $this->getElement('ENDDATE')->setValue(date('d/m/Y'));
        $this->getElement('COMPLETED')->setValue('-1');
        $this->getElement('TODOS_TYPE')->setValue('-1');
        $this->getElement('SELECT_ASSIGNED_TO')->setValue('-1');
    }

    protected function getTodoTypes(Application_Model_CommonFunctions $functions)
    {
        $types = explode("\n", $functions->T('setting_file_todo_types'));
        $todo_types['-1'] = $functions->T('all_c');
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
            '-1'  => $functions->T('all_c'),
            '1' => $functions->T('yes_c'),
            '0' => $functions->T('no_c'),
        );
    }

    protected function getAssignedTos(Application_Model_CommonFunctions $functions)
    {
        $types = explode("\n", $functions->T('todo_users_list'));
        $users_list['-1'] = $functions->T('all_c');
        foreach($types as $type )
        {
            list($code,$name) = explode(",",$type);
            $users_list[$code] = $name;
        }

        return $users_list;
    }

}

