<?php

class Application_Form_ClientInvoiceParams extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('select', 'CONDITION_TYPE', array('label' => $functions->T('condition_type_c'), 'required' => true, 'MultiOptions' => $this->getConditionTypes()));
        $this->addElement('text', 'FROM_DATE', array('label' => $functions->T('from_date_c'), 'required' => true));
        $this->addElement('text', 'END_VALUE', array('label' => $functions->T('end_value_c'), 'required' => true, 'validators' => array('float'), 'size' => 15));
        $this->addElement('text', 'INVOICE_PERCENT', array('label' => $functions->T('percent_c'), 'required' => true, 'validators' => array('float'), 'size' => 15));
        $this->addElement('text', 'INVOICE_MINIMUM', array('label' => $functions->T('min_cost_c'), 'required' => true, 'validators' => array('float'), 'size' => 15));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

    protected function getConditionTypes()
    {
        return array(
            'C' => 'C - Commissie',
            'D' => 'D - Dossier',
            'O' => 'O - Andere',
            'P' => 'P - Adresaanvraag',
        );
    }
}

