<?php

class Application_Form_FileEditInvoice extends Zend_Form {

    public function init() {
        global $db;

        $stateModel = new Application_Model_States();

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $helper = new Application_Form_FormHelper();
        $this->addElement('hidden', 'REFERENCE_ID', array('required' => true));
        $this->addElement('text', 'REFERENCE_TYPE', array('label' => $functions->T('type_c') ,'size' => 15, 'required' => true));
        $this->addElement('text', 'TRAIN_TYPE', array('label' => $functions->T('train_type_c') ,'size' => 15, 'required' => false));
        $this->addElement('text', 'REFERENCE', array('label' => $functions->T('factuurnummer_c') ,'size' => 15, 'required' => true));
        $this->addElement('select', 'STATE_ID', array('label' => $functions->T('State_c'), 'required' => true, 'MultiOptions' => $functions->db2array($stateModel->getStatesForSelect())));
        $this->addElement('text', 'REFUND_STATEMENT', array('label' => $functions->T('Refund_statement_c') ,'size' => 15));
        $this->addElement('text', 'CONTRACT_UY', array('label' => $functions->T('contract_uy') ,'size' => 15));
        $this->addElement('text', 'CONTRACT_INSURED', array('label' => $functions->T('contract_insured') ,'size' => 15));
        $this->addElement('text', 'CONTRACT_UNDERWRITER', array('label' => $functions->T('contract_underwriter') ,'size' => 15));
        $this->addElement('text', 'CONTRACT_NUMBER', array('label' => $functions->T('contract_number') ,'size' => 15));
        $this->addElement('text', 'INVOICE_DATE',array('label' => $functions->T('factuurdatum_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'START_DATE',array('label' => $functions->T('vervaldatum_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'END_DATE',array('label' => $functions->T('end_date_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'AMOUNT',array('label' => $functions->T('amount_c'),'size' => 15, 'required' => true, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('select', 'AUTO_CALCULATE',array('label' => $functions->T('auto_calculate_c'),'MultiOptions' => array("1" => $functions->T('yes_c'), "0" => $functions->T('no_c')),'required' => true,'OnChange' => "autoCalculate()"));
        $this->addElement('text', 'INTEREST',array('label' => $functions->T('interest_c'),'size' => 15, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('text', 'COSTS',array('label' => $functions->T('costs_c'),'size' => 15, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('text', 'INTEREST_PERCENT',array('label' => $functions->T('Interest_percent_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('text', 'COST_PERCENT',array('label' => $functions->T('Cost_percent_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('text', 'INTEREST_MINIMUM',array('label' => $functions->T('Interest_minimum_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('text', 'COST_MINIMUM',array('label' => $functions->T('Cost_minimum_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('select', 'DISPUTE',array('label' => $functions->T('dispute_c'),'MultiOptions' => array("1" =>$functions->T('yes_c'), "0" => $functions->T('no_c')),'required' => true , 'OnChange' => "dispute()"));
        $this->addElement('textarea', 'DISPUTE_COMMENT', array('label' => $functions->T('dispute_comment_c'), 'rows' => 5, 'cols' => 45, 'required' => false));

        $disputeStatusses = $this->getSettingsMultiOptions($functions, "setting_dispute_statusses");
        $disputeAssignees = $this->getSettingsMultiOptions($functions, "setting_dispute_assignees");

        $this->addElement('select', 'DISPUTE_ASSIGNEE', array('label' => $functions->T('dispute_assignee_c'), 'required' => false, 'MultiOptions' => $disputeAssignees));
        $this->addElement('select', 'DISPUTE_STATUS', array('label' => $functions->T('dispute_status_c'), 'required' => false, 'MultiOptions' => $disputeStatusses));
        $this->addElement('text', 'DISPUTE_DATE',array('label' => $functions->T('dispute_date_c'),'size' => 15, 'required' => false,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'DISPUTE_DUEDATE',array('label' => $functions->T('dispute_duedate_c'),'size' => 15, 'false' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'DISPUTE_ENDED_DATE',array('label' => $functions->T('dispute_ended_date_c'),'size' => 15, 'required' => false,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'DISPUTE_AMOUNT',array('label' => $functions->T('dispute_amount_c'),'size' => 15, 'required' => false,'validators'=>array ('validators'=> array($helper->getFloatValidator()))));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
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

