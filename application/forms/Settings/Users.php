<?php

class Application_Form_Settings_Users extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $clientModel = new Application_Model_Clients();
        $collectorsModel = new Application_Model_Collectors();

        $this->addElement('text', 'CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'maxlength' => 20, 'size' => 50));
        $this->addElement('password', 'PASS', array('label' => $functions->T('Password_c'), 'required' => false, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'NAME', array('label' => $functions->T('name_c'), 'required' => true, 'maxlength' => 200, 'size' => 50));
        $this->addElement('text', 'E_MAIL', array('label' => $functions->T('email_c'), 'required' => true, 'maxlength' => 150, 'size' => 50));
        $this->addElement('select', 'RIGHTS', array('label' => $functions->T('Right_level_c'), 'required' => true, 'MultiOptions' => array(4 => '4 - ' . $functions->T('Admin_user_c'), 5 => '5 - ' . $functions->T('Client_user_c'), 6 => '6 - ' . $functions->T('collector_c'))));
        $this->addElement('select', 'CLIENT_ID', array('label' => $functions->T('klantencode_c'), 'required' => false, 'MultiOptions' => $functions->db2array($clientModel->getArrayClients())));
        $this->addElement('select', 'COLLECTOR_ID', array('label' => $functions->T('collector_c'), 'required' => false, 'MultiOptions' => $functions->db2array($collectorsModel->getCollectorsForSelect())));
        $this->addElement('multiCheckbox', 'USER_RIGHTS', array('label' => $functions->T('Specific_rights_c'), 'required' => false, 'MultiOptions' => $this->getSpecificRights($functions)));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

    protected function getSpecificRights(Application_Model_CommonFunctions $functions)
    {
        return array(
            'DELETE_FILE' => $functions->T('delete_file_c'),
        );
    }
}

