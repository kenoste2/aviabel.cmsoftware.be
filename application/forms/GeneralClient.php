<?php

class Application_Form_GeneralClient extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();
        $languages = new Application_Model_Languages();
        $countries = new Application_Model_Countries();
        $clients = new Application_Model_Clients();
        $train = new Application_Model_Train();

        $belgiumId = $countries->getCountryByCode('BE');

        $this->addElement('submit', 'SUBMIT', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
            'class' => 'submit',
        ));


        $this->addElement('text', 'CODE', array('label' => $functions->T('code_c'), 'size' => 50, 'required' => true));
        $this->addElement('password', 'PASSWORD', array('label' => $functions->T('Password_c'), 'size' => 50, 'required' => false));
        $this->addElement('password', 'PASSWORD2', array('label' => $functions->T('confirm_pass_c'), 'size' => 50, 'required' => false));
        $this->addElement('text', 'NAME', array('label' => $functions->T('name_c'), 'size' => 50));
        $this->addElement('text', 'ADDRESS', array('label' => $functions->T('address_c'), 'size' => 50, 'required' => true));
        $this->addElement('text', 'ZIP_CODE', array('label' => $functions->T('zipcode_c'), 'size' => 8, 'required' => true));
        $this->addElement('text', 'CITY', array('label' => $functions->T('city_c'), 'size' => 34, 'required' => true));
        $this->addElement('select', 'COUNTRY_ID', array('label' => $functions->T('country_c'), 'MultiOptions' => $functions->db2array($countries->getCountries(), false)));
        $this->addElement('select', 'LANGUAGE_ID', array('label' => $functions->T('language_c'), 'MultiOptions' => $functions->db2array($languages->getLanguages(), false)));
        $this->addElement('select', 'MAINCLIENT', array('label' => $functions->T('hoofdklant_c'), 'MultiOptions' => $functions->db2array($clients->getArrayClients(), false)));
        $this->addElement('text', 'BANK_ACCOUNT_NR', array('label' => $functions->T('Account_c'), 'size' => 50));
        $this->addElement('text', 'E_MAIL', array('label' => $functions->T('email_c'), 'size' => 50, 'validators' => array(array('EmailAddress')),));
        $this->addElement('text', 'TELEPHONE', array('label' => $functions->T('tel_c'), 'size' => 50));
        $this->addElement('text', 'TELEFAX', array('label' => $functions->T('fax_c'), 'size' => 50));
        $this->addElement('textarea', 'REMARKS', array('label' => $functions->T('remark_c'), 'required' => false, 'rows' => 5, 'cols' => 50));
        $this->addElement('select', 'TRAIN_TYPE', array('label' => $functions->T('train_type_c'), 'MultiOptions' => $functions->db2array($train->getTrainTypes(), false)));

        $this->addElement('text', 'VAT_NO', array('label' => $functions->T('vat_c'), 'size' => 50, 'required' => false));
        $this->addElement('text', 'INVOICE_CONTACT', array('label' => $functions->T('name_c'), 'size' => 50));
        $this->addElement('text', 'INVOICE_ADDRESS', array('label' => $functions->T('Address_c'), 'size' => 50));
        $this->addElement('text', 'INVOICE_ZIP_CODE', array('label' => $functions->T('zipcode_c'), 'size' => 8, 'required' => false));
        $this->addElement('text', 'INVOICE_CITY', array('label' => $functions->T('city_c'), 'size' => 34, 'required' => false));
        $this->addElement('select', 'INVOICE_COUNTRY_ID', array('label' => $functions->T('country_c'), 'MultiOptions' => $functions->db2array($countries->getCountries(), false)));
        $confirm = array(1 => $functions->T('yes_c'), 0 => $functions->T('no_c'));
        $this->addElement('radio', 'COMPENSATED', array(
            'label' => $functions->T('Compensated_c'),
            'required' => false,
            'MultiOptions' => $confirm,
        ));

        $helper = new Application_Form_FormHelper();
        $this->addElement('text', 'CURRENT_INTREST_PERCENT', array('label' => $functions->T('Interest_percent_c'), 'size' => 15, 'validators' => array($helper->getFloatValidator())));
        $this->addElement('text', 'CURRENT_INTREST_MINIMUM', array('label' => $functions->T('Interest_minimum_c'), 'size' => 15, 'validators' => array($helper->getFloatValidator())));
        $this->addElement('text', 'CURRENT_COST_PERCENT', array('label' => $functions->T('cost_percent_c'), 'size' => 15, 'validators' => array($helper->getFloatValidator())));
        $this->addElement('text', 'CURRENT_COST_MINIMUM', array('label' => $functions->T('cost_minimum_c'), 'size' => 15, 'validators' => array($helper->getFloatValidator())));
        $this->addElement('text', 'ARTICLE', array('label' => $functions->T('article_c'), 'size' => 50));
        $this->addElement('text', 'COURT', array('label' => $functions->T('court_c'), 'size' => 50));
        $this->addElement('text', 'ACTIVITIES', array('label' => $functions->T('activities_c'), 'size' => 50));
        $this->addElement('textarea', 'TEMPLATE_FOOTER', array('label' => $functions->T('template_footer_c'), 'size' => 50, 'rows' => 5, 'cols' => 50));



        $this->addDisplayGroup(array(
            'CURRENT_INTREST_PERCENT', 'CURRENT_INTREST_MINIMUM', 'CURRENT_COST_PERCENT', 'CURRENT_COST_MINIMUM', 'SUBMIT')
                , 'group3', array('legend' => $functions->T("Client_Conditions_c")));
        $group3 = $this->getDisplayGroup('group3');
        $group3->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:right;'))
        ));
        
        
        $this->getElement('COUNTRY_ID')->setValue($belgiumId);
        $this->getElement('INVOICE_COUNTRY_ID')->setValue($belgiumId);
    }

}

