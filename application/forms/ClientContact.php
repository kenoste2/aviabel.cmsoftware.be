<?php

class Application_Form_ClientContact extends Zend_Form {

    public function init($debtorId = false) {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $languageModel = new Application_Model_Languages();

        $this->addElement('text', 'FUNCTION_DESCRIPTION', array('label' => $functions->T('function_c'), 'required' => true));
        $this->addElement('text', 'NAME', array('label' => $functions->T('name_c'), 'required' => true));
        $this->addElement('text', 'EMAIL', array('label' => $functions->T('email_c'), 'required' => true, 'validators' => array('EmailAddress')));
        $this->addElement('text', 'TEL', array('label' => $functions->T('tel_c'), 'required' => false));
        $this->addElement('text', 'FAX', array('label' => $functions->T('fax_c'), 'required' => false));
        $this->addElement('select', 'LANGUAGE_CODE_ID', array('label' => $functions->T('language_c'), 'MultiOptions' => $functions->db2array($languageModel->getLanguages(), false), 'required' => true));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

