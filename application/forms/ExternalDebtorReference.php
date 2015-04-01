<?php

class Application_Form_ExternalDebtorReference extends Zend_Form {

    private $_reference;

    function __construct($reference){
        $this->_reference = $reference;

        parent::__construct();
    }

    function init() {
        parent::init();

        $functions = new Application_Model_CommonFunctions();

        $this->addElement('textarea', "DEBTOR_DISPUTE_COMMENT_{$this->_reference->REFERENCE_ID}", array(
                    'label' => $functions->T('dispute_comment_c'),
                    'rows' => 8,
                    'cols' => 45,
                    'value' => $this->_reference->DEBTOR_DISPUTE_COMMENT));
        $this->addElement('submit', $functions->T('add_comment_c'));
    }
}
