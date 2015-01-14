<?php

class Application_Form_FileAddpayment extends Zend_Form {

    public function init($fileId = false) {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        
        $accounts = $db->get_results("select ACCOUNT_ID,DESCRIPTION from ACCOUNTS\$ACCOUNTS where VISIBLE='Y' order by DESCRIPTION", ARRAY_N);

        $this->addElement('select', 'ACCOUNT_ID', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($accounts),
            'label' => $functions->T('Account_c'),
        ));

        $helper = new Application_Form_FormHelper();
        
        $this->addElement('text', 'VALUTA_DATE', array('label' => $functions->T('date_c'), 'size' => 15, 'required' => true, 'validators' => array(array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'AMOUNT', array('label' => $functions->T('amount_c'), 'size' => 15, 'required' => false, 'validators' => array($helper->getFloatValidator())));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('description_c'), 'size' => 15, 'validators' => array('notEmpty')));
        
        $this->addElement('select', 'REFERENCE_ID', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($references),
            'label' => $functions->T('reference_c'),
        ));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

