<?php

class Application_Form_Disputes extends Zend_Form
{

    public function init()
    {

        global $db;
        $functions = new Application_Model_CommonFunctions();

        $this->setMethod('post');

        $this->addElement('select', 'DISPUTE_STATUS_ID',
            array('label' => $functions->T('dispute_status_c'))); //TODO: add dispute status choices
        $this->addElement('select', 'DISPUTE_OWNER_ID',
            array('label' => $functions->T('dispute_owner_c'))); //TODO: add dispute owner choices
        $this->addElement('text', 'DATE_STARTED_FROM',
            array('label' => $functions->T('date_started_from_c'))); //TODO: add date picker classes and logic
        $this->addElement('text', 'DATE_STARTED_TILL',
            array('label' => $functions->T('date_started_till_c'))); //TODO: add date picker classes and logic
        $this->addElement('text', 'DATE_ENDED_FROM',
            array('label' => $functions->T('date_ended_from_c'))); //TODO: add date picker classes and logic
        $this->addElement('text', 'DATE_ENDED_TILL',
            array('label' => $functions->T('date_ended_till_c'))); //TODO: add date picker classes and logic
        $this->addElement('text', 'EXPIRY_DATE_FROM',
            array('label' => $functions->T('expiry_date_from_c'))); //TODO: add date picker classes and logic
        $this->addElement('text', 'EXPIRY_DATE_TILL',
            array('label' => $functions->T('expiry_date_till_c'))); //TODO: add date picker classes and logic
    }
}
