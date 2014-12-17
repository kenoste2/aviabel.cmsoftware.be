<?php

class Application_Form_FileGeneralDebtor extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $this->setMethod('post');
        $this->addElement('hidden', 'DEBTORFORM', array("value" => 1));
        $this->addElement('text', 'AFNAME_NAAM', array('label' => $functions->T('send_name_c'), 'size' => 30));
        $this->addElement('textarea', 'AFNAME_ADRES', array('label' => $functions->T('send_address_c'), 'rows' => 3, 'cols' => 25));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

