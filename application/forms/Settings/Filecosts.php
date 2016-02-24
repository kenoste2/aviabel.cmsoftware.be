<?php

class Application_Form_Settings_Filecosts extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'CODE', array('label' => $functions->T('code_c'), 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('name_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));
        $this->addElement('text', 'AMOUNT', array('label' => $functions->T('Amount_c'), 'required' => false, 'validators' => array('float')));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

