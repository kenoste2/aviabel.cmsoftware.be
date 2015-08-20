<?php

class Application_Form_FileAddInvoice extends Zend_Form {

    public function init() {
        global $db;

        $stateModel = new Application_Model_States();

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $this->addElement('text', 'REFERENCE', array('label' => $functions->T('factuurnummer_c') ,'size' => 15, 'required' => true));
        $this->addElement('select', 'STATE_ID', array('label' => $functions->T('State_c'), 'required' => true, 'MultiOptions' => $functions->db2array($stateModel->getStatesForSelect())));
        $this->addElement('text', 'INVOICE_DATE',array('label' => $functions->T('factuurdatum_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'START_DATE',array('label' => $functions->T('vervaldatum_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'AMOUNT',array('label' => $functions->T('amount_c'),'size' => 15, 'required' => true, 'validators'=> array('float')));
        $this->addElement('select', 'AUTO_CALCULATE',array('label' => $functions->T('auto_calculate_c'),'MultiOptions' => array("1" =>$functions->T('yes_c'), "0" => $functions->T('no_c')),'required' => true,'OnChange' => "autoCalculate()"));
        $this->addElement('text', 'INTEREST',array('label' => $functions->T('interest_c'),'size' => 15, 'validators'=> array('float')));
        $this->addElement('text', 'COSTS',array('label' => $functions->T('costs_c'),'size' => 15, 'validators'=> array('float')));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

