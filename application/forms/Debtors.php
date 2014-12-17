<?php

class Application_Form_Debtors extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'name', array('size'=> 50));
        $this->addElement('text', 'vat_no', array('size'=> 50));
        $this->addElement('text', 'address', array('size'=> 50));
        $this->addElement('text', 'zip_code', array('size'=> 8));
        $this->addElement('text', 'city', array('size'=> 30));
        $this->addElement('hidden', 'search_debtor', array('value' => 1));
        
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
    }

}

