<?php

class Application_Form_Settings_Trajects extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $actionModel = new Application_Model_Actions();
        $stateModel = new Application_Model_States();
        $templateModel = new Application_Model_Templates();

        $this->addElement('text', 'TRAIN_TYPE', array('label' => $functions->T('train_type_c'), 'required' => false, 'maxlength' => 20, 'size' => 50));
        $numbers = array();
        for ($i=1;$i<=20;$i++) {
            $numbers [$i] = $i;
        }
        $this->addElement('select', 'ORDER_CYCLE', array('label' => $functions->T('order_c'), 'required' => true,'MultiOptions' => $numbers));

        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('Description_c'), 'required' => true));
        $this->addElement('select', 'OPEN_FILES', array('label' => $functions->T('actief_c'), 'required' => false, 'MultiOptions' => $this->getOpenFiles($functions)));
        $this->addElement('text', 'DAYS', array('label' => $functions->T('Last_actions_days_ago_c'), 'required' => true));
        $this->addElement('multiCheckbox', 'ACTIONBOX', array('label' => $functions->T('Last_action_c'), 'required' => false,'separator' => ' ', 'MultiOptions' => $functions->db2array($actionModel->getActionsForSelectWithCode(), false)));
        $this->addElement('multiCheckbox', 'STATEBOX', array('label' => $functions->T('State_code_c'),'separator' => ' ', 'required' => false, 'MultiOptions' => $functions->db2array($stateModel->getStatesForSelectByCode(), false)));
        $this->addElement('select', 'PAYMENTS', array('label' => $functions->T('pay_after_c'), 'required' => false, 'MultiOptions' => array("Y" =>$functions->T('yes_c'), "N" => $functions->T('no_c'), "BOTH" => $functions->T('Both_options_c'))));
        $this->addElement('select', 'OTHER_ACTIONS', array('label' => $functions->T('other_actions_c'), 'required' => false, 'MultiOptions' => array("Y" =>$functions->T('yes_c'), "N" => $functions->T('no_c'), "BOTH" => $functions->T('Both_options_c'))));
        $this->addElement('text', 'EXTRA_RULES', array('label' => $functions->T('Extra_rules_c'), 'required' => false));
        $this->addElement('note', 'EXTRA_RULES_TEXT', array('label' => "ex. AMOUNT>'1000' AND (CLIENT_CODE='101' OR CLIENT_CODE='102')<br> <u>" . $functions->T('followingfields_c') . ":</u> AMOUNT,TOTAL,PAYABLE,PAYED_TOTAL,CLIENT_CODE,COLLECTOR_CODE", 'size' => 15, 'disabled' => true));

        $this->addElement('select', 'SETACTION', array('label' => $functions->T('Add_actions_c'), 'required' => false, 'MultiOptions' => $functions->db2array($actionModel->getActionsForSelect())));
        $this->addElement('select', 'STATE_ID', array('label' => $functions->T('Set_state_code_c'), 'required' => false, 'MultiOptions' => $functions->db2array($stateModel->getStatesForSelect())));
        $this->addElement('select', 'TEMPLATE_ID', array('label' => $functions->T('Set_template_c'), 'required' => false, 'MultiOptions' => $functions->db2array($templateModel->getTemplatesForSelect())));
        $this->addElement('select', 'ACTIEF', array('label' => $functions->T('actief_c'), 'required' => false, 'MultiOptions' => array("1" =>$functions->T('yes_c'), "0" => $functions->T('no_c'))));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));

        $this->getElement('PAYMENTS')->setValue('N');
        $this->getElement('OTHER_ACTIONS')->setValue('N');
        $this->getElement('DAYS')->setValue('21 ');

        $this->makeDisplayGroups();
    }

    protected function makeDisplayGroups()
    {
        $this->addDisplayGroup(array('ACTIEF', 'TRAIN_TYPE', 'CODE', 'DESCRIPTION', 'OPEN_FILES', 'DAYS', 'ACTIONBOX',
                'STATEBOX', 'PAYMENTS','OTHER_ACTIONS', 'EXTRA_RULES', 'EXTRA_RULES_TEXT'), 'group1');
        $group1 = $this->getDisplayGroup('group1');
        $group1->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div'))
        ));

        $this->addDisplayGroup(array('SETACTION', 'STATE_ID', 'TEMPLATE_ID'), 'group2');
        $group2 = $this->getDisplayGroup('group2');
        $group2->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div'))
        ));

        $this->addDisplayGroup(array('submit'), 'group3');
        $group3 = $this->getDisplayGroup('group3');
        $group3->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div'))
        ));
    }

    protected function getOpenFiles(Application_Model_CommonFunctions $functions)
    {
        return array(
            'OPEN' => $functions->T('Open_c'),
            'CLOSED' => $functions->T('closed_train_c'),
            'BOTH' => $functions->T('Both_c'),
        );
    }
}

