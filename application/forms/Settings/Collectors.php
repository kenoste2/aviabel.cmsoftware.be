<?php

class Application_Form_Settings_Collectors extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $languageModel = new Application_Model_Languages();
        $countryModel = new Application_Model_Countries();

        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'size' => 30, 'maxlength' => 20));
        $this->addElement('text', 'NAME', array('label' => $functions->T('Name_c'), 'required' => true, 'size' => 50, 'maxlength' => 200));
        $this->addElement('text', 'ADDRESS', array('label' => $functions->T('Address_c'), 'required' => true, 'size' => 50, 'maxlength' => 200));
        $this->addElement('text', 'ZIP_CODE', array('label' => $functions->T('zip_code_c'), 'required' => true, 'size' => 15));
        $this->addElement('text', 'CITY', array('label' => $functions->T('city_c'), 'required' => true, 'size' => 30));
        $this->addElement('select', 'COUNTRY_ID', array('label' => $functions->T('country_c'), 'required' => true, 'MultiOptions' => $functions->db2array($countryModel->getCountriesForSelect())));
        $this->addElement('select', 'LANGUAGE_ID', array('label' => $functions->T('language_c'), 'required' => true, 'MultiOptions' => $functions->db2array($languageModel->getLanguages())));
        $this->addElement('text', 'EMAIL', array('label' => $functions->T('email_c'), 'required' => true, 'size' => 30, 'maxlength' => 100));
        $this->addElement('text', 'TELEPHONE', array('label' => $functions->T('telephone_c'), 'required' => false, 'size' => 30, 'maxlength' => 100));
        $this->addElement('checkbox', 'EXTERN', array('label' => $functions->T('external_c'), 'required' => true, 'size' => 30));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

