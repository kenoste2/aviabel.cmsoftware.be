<?php

class Application_Form_Settings_Templates extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $actionModel = new Application_Model_Actions();

        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('Description_c'), 'required' => true, 'maxlength' => 100, 'size' => 50));
        $this->addElement('select', 'ACTION_ID', array('label' => $functions->T('Action_c'), 'required' => false, 'MultiOptions' => $functions->db2array($actionModel->getActionsForSelect())));

        $this->addElement('select', 'TEMPLATE_FOR', array('label' => $functions->T('template_for_c'), 'required' => false, 'MultiOptions' => $this->getTemplateFor($functions)));
        $this->addElement('multiCheckbox', 'TEMPLATE_MODULES', array('label' => $functions->T('Activated_modules_c'), 'required' => false, 'MultiOptions' => $this->getModules()));

        $this->addElement('textarea', 'TEXT_NL', array('label' => $functions->T('textinhoud_c') . ' NL', 'required' => false, 'rows' => '15', 'cols' => '120'));
        $this->addElement('textarea', 'TEXT_FR', array('label' => $functions->T('textinhoud_c') . ' FR', 'required' => false, 'rows' => '15', 'cols' => '120'));
        $this->addElement('textarea', 'TEXT_EN', array('label' => $functions->T('textinhoud_c') . ' EN', 'required' => false, 'rows' => '15', 'cols' => '120'));
        $this->addElement('textarea', 'TEXT_DE', array('label' => $functions->T('textinhoud_c') . ' DE', 'required' => false, 'rows' => '15', 'cols' => '120'));

        $this->addElement('textarea', 'TEXT_SMS_NL', array('label' => $functions->T('textinhoud_sms_c') . ' NL', 'required' => false, 'rows' => '15', 'cols' => '120'));
        $this->addElement('textarea', 'TEXT_SMS_FR', array('label' => $functions->T('textinhoud_sms_c') . ' FR', 'required' => false, 'rows' => '15', 'cols' => '120'));
        $this->addElement('textarea', 'TEXT_SMS_EN', array('label' => $functions->T('textinhoud_sms_c') . ' EN', 'required' => false, 'rows' => '15', 'cols' => '120'));
        $this->addElement('textarea', 'TEXT_SMS_DE', array('label' => $functions->T('textinhoud_sms_c') . ' DE', 'required' => false, 'rows' => '15', 'cols' => '120'));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

    protected function getModules(){
        $moduleModel = new Application_Model_TemplateModules();
        $modules = array();

        foreach ($moduleModel->getModules() as $module) {
            $modules[$module->ID] = $module->NL . ($module->VARIABELEN ? ' (use: ' . $module->VARIABELEN . ')' : '');
        }

        return $modules;
    }

    protected function getTemplateFor(Application_Model_CommonFunctions $functions)
    {
        return array(
            'D' => $functions->T('debtor_c'),
            'C' => $functions->T('client_c'),
            'P' => $functions->T('Contact_c'),
        );
    }
}

