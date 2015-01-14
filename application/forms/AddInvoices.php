<?php

class Application_Form_AddInvoices extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();
        $helper = new Application_Form_FormHelper();

        $this->addElement('text', 'reference', array('label'=> $functions->T('factuurnummer_c'),'size' => 15, 'required' => true));
        $this->addElement('text', 'invoice_date',array('label'=> $functions->T('factuurdatum_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'start_date',array('label'=> $functions->T('vervaldatum_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'amount',array('label'=> $functions->T('saldo_c'),'size' => 15, 'required' => true, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
        
    }

}

