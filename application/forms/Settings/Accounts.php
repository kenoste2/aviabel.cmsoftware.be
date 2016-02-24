<?php

class Application_Form_Settings_Accounts extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true , 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'VALUTA', array('label' => $functions->T('valuta'), 'required' => false , 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('Description_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));
        $this->addElement('text', 'ACCOUNT_NR', array('label' => $functions->T('Accountnr_c'), 'required' => true, 'maxlength' => 25, 'size' => 50));
        $this->addElement('text', 'BIC', array('label' => 'BIC', 'required' => false, 'maxlength' => 25, 'size' => 50));
        $this->addElement('select', 'IN_HOUSE', array('label' => $functions->T('Inhouse_c'), 'required' => false, 'MultiOptions' => array("1" =>$functions->T('yes_c'), "0" => $functions->T('no_c'))));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

