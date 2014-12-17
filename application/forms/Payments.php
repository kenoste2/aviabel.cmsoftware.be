<?php

class Application_Form_Payments extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $accountModel = new Application_Model_Accounts();

        $this->addElement('text', 'FILE_NR', array('label' => $functions->T('Invoice_Nr_c'), 'size'=> 30, 'required' => true));
        $this->addElement('text', 'DEBTOR_NAME', array('label' => $functions->T('debtor_c'), 'size'=> 30, 'required' => true, 'attribs' => array('readonly'=>true)));
        $this->addElement('select', 'ACCOUNT_ID', array('label' => $functions->T('rekening_c'), 'MultiOptions'=> $functions->db2array($accountModel->getAccountsForSelect()), 'required' => true));
        $this->addElement('text', 'VALUTA_DATE', array('label' => $functions->T('datum_c'), 'size'=> 30, 'required' => true));
        $this->addElement('text', 'AMOUNT', array('label' => $functions->T('bedrag_c'), 'size'=> 15, 'required' => true));
        $this->addElement('textarea', 'DESCRIPTION', array('label' => $functions->T('beschrijving_c'), 'required' => false));

        
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

