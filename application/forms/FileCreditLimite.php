<?php

class Application_Form_FileCreditLimite extends Zend_Form
{
    public function init()
    {
        global $db;



        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');

        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();
        $this->addElement('hidden', 'CREDITLIMITEFORM', array("value" => 1));
        $this->addElement('text', 'OWN_CREDIT_LIMIT', array('label' => $functions->T('eigen_kredietlimiet_c'). "(€)", 'size' => 25, 'required' => false));

        if ($functions->moduleAccess('binformation')) {
            $this->addElement('note', 'PROVIDER_CREDIT_LIMIT', array('label' => $functions->T('kredietlimiet_provider_c'). "(€)", 'size' => 25, 'required' => false));
        }

        if ($functions->moduleAccess('insurance')) {
            $this->addElement('note', 'INSURANCE_CREDIT_LIMIT', array('label' => $functions->T('kredietlimiet_verzekering_c'). "(€)", 'size' => 25, 'required' => false));
        }


        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

