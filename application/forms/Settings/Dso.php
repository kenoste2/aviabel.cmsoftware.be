<?php

class Application_Form_Settings_Dso extends Zend_Form {

    public function init() {
        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $clientModel = new Application_Model_Clients();

        $activeClients = $clientModel->getAllClients();
        $clientItems = array('' => '-');
        foreach($activeClients[0] as $client) {
            $clientItems[$client->CLIENT_ID] = $client->NAME;
        }

        $this->addElement('select', 'CLIENT_ID', array('label' => $functions->T('client_c'), 'required' => true, 'MultiOptions' => $clientItems));
        $this->addElement('text', 'DSO_YEAR', array('label' => $functions->T('year_c'), 'required' => true, 'maxlength' => 20, 'size' => 10));
        $this->addElement('text', 'DSO_MONTH', array('label' => $functions->T('month_c'), 'required' => true, 'maxlength' => 20, 'size' => 10));
        $this->addElement('text', 'SALES', array('label' => $functions->T('turnover_c'), 'required' => true, 'maxlength' => 100, 'size' => 20));
        $this->addElement('text', 'DSO', array('label' => $functions->T('dso_c'), 'required' => false, 'maxlength' => 100, 'size' => 20));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

