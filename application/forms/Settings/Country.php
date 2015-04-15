<?php

class Application_Form_Settings_Country extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $helper = new Application_Form_FormHelper();

        $this->addElement('text', 'CODE', array('label' => $functions->T('code_c'), 'required' => true, 'maxlength' => 20,'size' => 50));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('name_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));
        $this->addElement('text', 'DELTA', array('label' => $functions->T('commission_delta_c'), 'required' => false, 'validators' => array($helper->getFloatValidator())));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

