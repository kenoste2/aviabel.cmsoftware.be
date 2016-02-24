<?php

class Application_Form_Settings_Texts extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'NAV', array('label' => 'nav', 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));

        $this->addElement('textarea', 'NL', array('label' => 'NL', 'required' => false, 'rows' => '3', 'cols' => '150'));
        $this->addElement('textarea', 'FR', array('label' => 'FR', 'required' => false, 'rows' => '3', 'cols' => '150'));
        $this->addElement('textarea', 'EN', array('label' => 'EN', 'required' => false, 'rows' => '3', 'cols' => '150'));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

