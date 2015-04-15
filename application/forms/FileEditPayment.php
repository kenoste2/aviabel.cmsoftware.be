<?php

class Application_Form_FileEditPayment extends Zend_Form {

    public function init($fileId = false) {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $helper = new Application_Form_FormHelper();
        
        $this->addElement('text', 'PAYMENT_DATE', array('label' => $functions->T('date_c'), 'size' => 15, 'required' => true, 'validators' => array(array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'COMMISSION', array('label' => $functions->T('commission_c'), 'size' => 15, 'validators' => array($helper->getFloatValidator())));
        
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

