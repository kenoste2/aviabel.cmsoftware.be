<?php

class Application_Form_Settings_Closestates extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('Description_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

