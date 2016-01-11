<?php

class Application_Form_Settings_AllowedMails extends Zend_Form {

    public function init() {
        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'TYPE', array('label' => $functions->T('type_c'), 'required' => true, 'maxlength' => 50));
        $this->addElement('text', 'EMAIL', array('label' => $functions->T('email_c'), 'required' => true, 'maxlength' => 100));
        $this->addElement('text', 'NAME', array('label' => $functions->T('name_c'), 'required' => true, 'maxlength' => 100));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

