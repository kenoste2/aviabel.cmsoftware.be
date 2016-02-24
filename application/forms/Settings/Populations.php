<?php

class Application_Form_Settings_Populations extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $helper = new Application_Form_FormHelper();

        $this->addElement('text', 'NAME', array('label' => $functions->T('Name_c'), 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'ADDRESS', array('label' => $functions->T('address_c'), 'required' => true, 'maxlength' => 200, 'size' => 50));
        $this->addElement('text', 'FAX', array('label' => $functions->T('fax_c'), 'required' => false, 'maxlength' => 25, 'size' => 50));
        $this->addElement('text', 'ZIP_CODE', array('label' => $functions->T('zip_code_c'), 'required' => true , 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'CITY', array('label' => $functions->T('city_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));
        $this->addElement('text', 'AMOUNT', array('label' => $functions->T('amount_population_c'), 'required' => false, 'size' => 15, 'validators' => array($helper->getFloatValidator())));
        $this->addElement('text', 'ACCOUNT_NO', array('label' => $functions->T('account_population_c'), 'required' => false, 'maxlength' => 25, 'size' => 50));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));

        //PAYEMENT_TYPE_ID = 3;
    }
}

