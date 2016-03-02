<?php

class Application_Form_ListsActionRemarks extends Zend_Form {

    public function init() {

        $functions = new Application_Model_CommonFunctions();
        $now = new DateTime();
        $actionsObj = new Application_Model_Actions();
        $usersObj = new Application_Model_Users();

        $this->addElement('text', 'FROM_DATE',array('label' => $functions->T('date_from_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))), 'value' => $now->format('d/m/Y')));
        $this->addElement('text', 'TO_DATE',array('label' => $functions->T('date_till_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))), 'value' => $now->format('d/m/Y')));
        $this->addElement('select', 'ACTION_ID', array('label' => $functions->T('Action_c'), 'required' => false, 'MultiOptions' => $functions->db2array($actionsObj->getActionsForSelect())));
        $this->addElement('select', 'USER_ID', array('label' => $functions->T('user_c'), 'required' => false, 'MultiOptions' => $functions->db2array($usersObj->getUsersForSelect())));

        $this->addDisplayGroup(array(
            'FROM_DATE', 'ACTION_ID'
        ), 'group1');

        $group1 = $this->getDisplayGroup('group1');
        $group1->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:left;padding:10px;'))
        ));
        $this->addDisplayGroup(array(
            'TO_DATE','USER_ID'
        ), 'group2');

        $group2 = $this->getDisplayGroup('group2');
        $group2->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:right;padding:10px;'))
        ));

        $this->addElement('submit', 'submit', array(
                'ignore' => true,
                'label' => $functions->T('search_c'),
            ));
    }
}