<?php

class Application_Form_Disputes extends Zend_Form
{

    public function init()
    {

        global $db;
        $functions = new Application_Model_CommonFunctions();

        $this->setMethod('post');

        $disputeStatusses = $this->getSettingsMultiOptions($functions, "setting_dispute_statusses");

        $disputeAssignees = $this->getSettingsMultiOptions($functions, "setting_dispute_assignees");


        $this->addElement('select', 'DISPUTE_STATUS',
            array('label' => $functions->T('dispute_status_c'), 'MultiOptions' => $disputeStatusses));
        $this->addElement('select', 'DISPUTE_ASSIGNEE',
            array('label' => $functions->T('dispute_assignee_c'), 'MultiOptions' => $disputeAssignees));
        $this->addElement('text', 'DATE_STARTED_FROM',
            array('label' => $functions->T('dispute_date_c') . ' ' . strtolower($functions->T('date_from_c')), 'class' => 'hasDatePicker', 'data-fieldtype' => 'datepicker', 'validators'=>array (array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('text', 'DATE_STARTED_TILL',
            array('label' => $functions->T('date_till_c'), 'class' => 'hasDatePicker', 'data-fieldtype' => 'datepicker', 'validators'=>array (array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('text', 'DATE_ENDED_FROM',
            array('label' => $functions->T('dispute_ended_date_c') . ' ' . strtolower($functions->T('date_from_c')) , 'class' => 'hasDatePicker', 'data-fieldtype' => 'datepicker', 'validators'=>array (array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('text', 'DATE_ENDED_TILL',
            array('label' => $functions->T('date_till_c'), 'class' => 'hasDatePicker', 'data-fieldtype' => 'datepicker', 'validators'=>array (array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('text', 'EXPIRY_DATE_FROM',
            array('label' => $functions->T('dispute_duedate_c'). ' ' . strtolower($functions->T('date_from_c')), 'class' => 'hasDatePicker', 'data-fieldtype' => 'datepicker', 'validators'=>array (array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('text', 'EXPIRY_DATE_TILL',
            array('label' => $functions->T('date_till_c'), 'class' => 'hasDatePicker', 'data-fieldtype' => 'datepicker', 'validators'=>array (array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('submit', 'submit', array('label' => $functions->T('search_c')) );
    }

    /**
     * @param $functions
     * @param $setting
     * @return array
     */
    public function getSettingsMultiOptions($functions, $setting)
    {
        $disputeStatussesSetting = $functions->getUserSetting($setting);
        $disputesStatussesNonKeyed = array();
        $explodedSetting = explode("\n", $disputeStatussesSetting);
        foreach($explodedSetting as $setting) {
            $disputesStatussesNonKeyed []= trim($setting);
        }
        $disputesStatussesKeyed = array_combine($disputesStatussesNonKeyed, $disputesStatussesNonKeyed);
        $disputeStatusses = array_merge(array('' => '-'), $disputesStatussesKeyed);
        return $disputeStatusses;
    }
}
