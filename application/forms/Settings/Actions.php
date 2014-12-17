<?php

class Application_Form_Settings_Actions extends Zend_Form {

    public function init() {
        global $db;

        //$this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $stateModel = new Application_Model_States();
        $costsModel = new Application_Model_Filecosts();


        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('Description_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));
        $this->addElement('select', 'FILE_STATE_ID', array('label' => $functions->T('State_c'), 'required' => false, 'MultiOptions' => $functions->db2array($stateModel->getStatesForSelect())));
        $this->addElement('select', 'COST_ID', array('label' => $functions->T('costs_c'), 'required' => false, 'MultiOptions' => $functions->db2array($costsModel->getFilecostsForSelect())));
        $this->addElement('select', 'VISIBLE', array('label' => $functions->T('pos_add_c'), 'required' => false,'MultiOptions' => array("1" =>$functions->T('yes_c'), "0" => $functions->T('no_c'))));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));

    }
}

