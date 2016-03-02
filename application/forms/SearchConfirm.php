<?php

class Application_Form_SearchConfirm extends Zend_Form
{

    public function init()
    {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();


        $confirmObj = new Application_Model_ConfirmActions();




        $this->addElement('select', 'ACTION_ID', array(
            'label' => $functions->T('code_c'),
            'required' => false,
            'MultiOptions' => $functions->db2array($confirmObj->getActionsToBeConfirmed(), true),
            'separator' => ' ',
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
    }
}

