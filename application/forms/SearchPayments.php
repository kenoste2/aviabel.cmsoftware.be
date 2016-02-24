<?php

class Application_Form_SearchPayments extends Zend_Form
{

    public function init()
    {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();


        $clientsObj = new Application_Model_Clients();

        $this->addElement('text', 'FILE_REFERENCE', array('label' => $functions->T('relationCode_c'), 'size' => 25, 'required' => false));
        $collectorObj = new Application_Model_Collectors();
        $this->addElement('select', 'COLLECTOR_ID', array(
            'label' => $functions->T('collector_c'),
            'required' => false,
            'MultiOptions' => $functions->db2array($collectorObj->getCollectorsForSelect(), true),
            'separator' => ' ',
        ));        $this->addElement('text', 'STARTDATE', array('label' => $functions->T('aanmaakdatum_c'), 'size' => 20));
        $this->addElement('text', 'ENDDATE', array('label' => $functions->T('tot_c'), 'size' => 20));
        //$this->addElement('text', 'CLIENT', array('label' => $functions->T('client_c'), 'size' => 20));
        $this->addElement('select', 'CLIENT', array('label' => $functions->T('client_c'), 'MultiOptions' =>$functions->db2array($clientsObj->getArrayClients())));
        $this->addElement('select', 'FOR', array('label' => $functions->T('voor_c'), 'MultiOptions' => $this->getFor($functions)));
        $this->addElement('select', 'ACCOUNT_ID', array('label' => $functions->T('rekening_c'), 'MultiOptions' => $this->getAccounts($functions)));

        $this->addElement('hidden', 'search_payment', array('value' => 1));




         $this->addDisplayGroup(array('FILE_REFERENCE','STARTDATE', 'CLIENT', 'COMMISSION', 'ACCOUNT_ID'), 'group1');
        $this->getDisplayGroup('group1')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:left;padding:10px;'))
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
        $this->addDisplayGroup(array('COLLECTOR_ID','ENDDATE', 'FOR','submit'), 'group2');
        $this->getDisplayGroup('group2')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:right;padding:10px;'))
        ));

        $this->getElement('STARTDATE')->setValue(date('d/m/Y'));
        $this->getElement('ENDDATE')->setValue(date('d/m/Y'));
        $this->getElement('FOR')->setValue('-1');
    }

    protected function getFor(Application_Model_CommonFunctions $functions)
    {
        return array(
            '-1'  => $functions->T('all_c'),
            'A' => $functions->T('amounts_c'),
            'C' => $functions->T('costs_c'),
            'I' => $functions->T('intrests_c'),
            '?' => $functions->T('unknown_c'),
        );
    }

    protected function getCommissions(Application_Model_CommonFunctions $functions)
    {
        return array(
            '-1'  => $functions->T('all_c'),
            '1' => $functions->T('yes_c'),
            '0' => $functions->T('no_c'),
        );
    }

    protected function getAccounts(Application_Model_CommonFunctions $functions)
    {
        $accountModel = new Application_Model_Accounts();
        return $functions->db2array($accountModel->getAccountsForSelect());
    }

}

