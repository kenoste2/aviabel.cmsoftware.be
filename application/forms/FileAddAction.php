<?php

class Application_Form_FileAddAction extends Zend_Form
{

    public function init($fileId = false)
    {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $this->addElement('text', 'ACTION_DATE', array('label' => $functions->T('Action_date_c'), 'size' => 15, 'required' => true,'onChange' => 'saveActionDate();', 'validators' => array(array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'ACTION_CODE', array('label' => $functions->T('Action_code_c'),'description' => $functions->T('action_code_forAll_c'), 'size' => 30,'onchange' => 'bp();'));
        $this->addElement('text', 'BP_STARTDATE', array('label' => $functions->T('First_payment_c'), 'size' => 15,'class' => 'BP','onchange' => 'savePaymentPlan();', 'validators' => array(array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('text', 'BP_NR_PAYMENTS', array('label' => $functions->T('Nr_of_payments_c'), 'size' => 15,'class' => 'BP','onchange' => 'savePaymentPlan();' ));
        $this->addElement('textarea', 'REMARKS', array('label' => $functions->T('Remarks_c'), 'size' => 15, 'rows' => 5, 'cols' => 30));

        $this->addElement('select', 'TEMPLATE_ID', array(
            'MultiOptions' => array(),
            'label' => $functions->T('Template_c'),
            'onchange' => 'templateContent();',
            'required' => false,
            'RegisterInArrayValidator' => false,
        ));


        $this->addElement('text', 'E_MAIL', array('label' => $functions->T('email_c'), 'size' => 50, 'validators' => array(array('EmailAddress')),));
        $this->addElement('textarea', 'ADDRESS', array('label' => $functions->T('bestemming_c'), 'size' => 15, 'rows' => 3, 'cols' => 30));
        $this->addElement('text', 'GSM', array('label' => $functions->T('gsm_c'), 'size' => 50));
        $options = array("POST" => $functions->T('post_c'), "EMAIL" => $functions->T('email_c'), "SMS" => $functions->T('sms_c'));

        $this->addElement('radio', 'VIA', array(
            'label' => $functions->T('via_c'),
            'MultiOptions' => $options,
            'onchange' => 'via();'
        ));

        $confirm = array(1 => $functions->T('yes_c'), 0 => $functions->T('no_c'));
        $this->addElement('radio', 'PRINTED', array(
            'label' => $functions->T('print_now_c'),
            'MultiOptions' => $confirm,
        ));

        $this->addElement('textarea', 'CONTENT', array('label' => $functions->T('inhoud_c'), 'size' => 15, 'rows' => 30, 'cols' => 120));
        $this->addElement('textarea', 'SMS_CONTENT', array('label' => $functions->T('inhoud_sms_c'), 'size' => 15, 'rows' => 10, 'cols' => 120));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

