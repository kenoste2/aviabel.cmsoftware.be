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
        $debtorExternalAccessObj = new Application_Model_DebtorExternalAccess();

        $enabled = $debtorExternalAccessObj->shouldBeEditableByDebtor($this->_reference->DISPUTE_STATUS);

        $fieldAttributes = array(
                    'label' => $functions->T('external_dispute_comment_c'),
                    'rows' => 8,
                    'cols' => 45,
                    'value' => $this->_reference->DEBTOR_DISPUTE_COMMENT);

        if(!$enabled) {
            $fieldAttributes['disabled'] = !$enabled;
        }

        $this->addElement('textarea', "DEBTOR_DISPUTE_COMMENT_{$this->_reference->REFERENCE_ID}", $fieldAttributes);

        if($enabled) {
            $this->addElement('submit', $functions->T('add_comment_c'));
        }
    }
}
