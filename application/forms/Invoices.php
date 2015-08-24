<?php

class Application_Form_Invoices extends Zend_Form {

    public function init() {

        global $db;
        $functions = new Application_Model_CommonFunctions();

        
        $this->setMethod('post');

        $this->addElement(
                'text', 'debtor', array(
            'required' => false,
        ));

        $this->addElement('text', 'client', array(
            'required' => false,
        ));

        $this->addElement('text', 'client_reference', array(
            'required' => false,
        ));

        $this->addElement('text', 'invoice', array(
            'required' => false,
        ));

        $collectors = $db->get_results("select COLLECTOR_ID,NAME from SYSTEM\$COLLECTORS where ACTIF='Y' order by NAME", ARRAY_N);

        $this->addElement('select', 'collector', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($collectors),
        ));

        $stateCodes = $db->get_results("select STATE_ID,CODE from FILES\$STATES where ACTIEF='1' order by CODE", ARRAY_N);

        $this->addElement('select', 'state_id', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($stateCodes),
        ));

        $this->addElement('select', 'dispute', array(
            'required' => false,
            'MultiOptions' => array("0" =>$functions->T('Both_c'),"Y" =>$functions->T('yes_c'), "N" => $functions->T('no_c')),
        ));

        $this->addElement('text', 'invoice_type', array(
            'required' => false,
        ));


        $functions = new Application_Model_CommonFunctions();
        
        $this->addElement('select', 'extra_field', array(
            'MultiOptions' => array(
                'AMOUNT' => $functions->T('amount_c'),
                'INVOICE_DATE' => $functions->T('factuurdatum_c'),
                'STATE_CODE' => $functions->T('state_code_c'),
                'DISPUTE_DATE' => $functions->T('dispute_date_c'),
                'DISPUTE_DUEDATE' => $functions->T('dispute_duedate_c'),
                'DISPUTE_ENDED_DATE' => $functions->T('dispute_ended_date_c'),
                'CONTRACT_NUMBER' => $functions->T('contract_number'),
                'CONTRACT_UY' => $functions->T('contract_uy'),
                'CONTRACT_INSURED' => $functions->T('contract_insured'),
                'CONTRACT_UNDERWRITER' => $functions->T('contract_underwriter'),
                )
            ));

         $this->addElement('select', 'extra_compare', array(
            'MultiOptions' => array(
                  '=' => $functions->T('equal_c'),
                '>=' => $functions->T('greater_then_c'),
                '<=' => $functions->T('smaller_then_c'),
                'containing' => $functions->T('containing_c'),
                )
            ));
       
        
        
        $this->addElement('text', 'extra_text', array());
        $this->addElement('checkbox', 'payed_invoices', array('value' => 'Y'));


        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
    }

}

