<?php

class Application_Form_FileAddCost extends Zend_Form {

    public function init($fileId = false) {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $costObj = new Application_Model_FilesCosts();

        $this->addElement('select', 'COST_ID', array(
            'label' => $functions->T('cost_c'),
            'required' => false,
            'MultiOptions' => $costObj->getCostsField(),
        ));
        $this->addElement('text', 'AMOUNT', array('label' => $functions->T('if_different_c'). " ".$functions->T('amount_c'), 'size' => 15, 'required' => false, 'validators' => array('float')));
        $this->addElement('text', 'AMOUNT_CLIENT', array('label' => $functions->T('if_different_c'). " ".$functions->T('client_amount_c'), 'size' => 15, 'required' => false, 'validators' => array('float')));
        $confirm = array(1 => $functions->T('yes_c'), 0 => $functions->T('no_c'));
        $this->addElement('radio', 'INVOICEABLE', array(
            'label' => $functions->T('invoiceable_c'),
            'required' => false,
            'MultiOptions' => $confirm,
        ));
        $this->addElement('textarea', 'EXTRA_INFO', array('label' => $functions->T('remark_c'), 'required' => false, 'rows' => 3, 'cols' => 30));

        
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

