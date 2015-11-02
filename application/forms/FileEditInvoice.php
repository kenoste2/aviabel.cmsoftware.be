<?php

class Application_Form_FileEditInvoice extends Zend_Form {

    public function init() {
        global $db;

        $stateModel = new Application_Model_States();

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        $helper = new Application_Form_FormHelper();
        $this->addElement('hidden', 'REFERENCE_ID', array('required' => true));
        $this->addElement('note', 'REFERENCE_TYPE', array('label' => $functions->T('type_c') ,'size' => 15));
        $this->addElement('note', 'TRAIN_TYPE', array('label' => $functions->T('train_type_c') ,'disabled' => true,'size' => 15));
        $this->addElement('note', 'REFERENCE', array('label' => $functions->T('factuurnummer_c') ,'size' => 15));
        $this->addElement('select', 'STATE_ID', array('label' => $functions->T('State_c'), 'disabled' => true, 'MultiOptions' => $functions->db2array($stateModel->getStatesForSelect())));
        $this->addElement('note', 'REFUND_STATEMENT', array('label' => $functions->T('Refund_statement_c') ,'size' => 15));
        $this->addElement('note', 'CONTRACT_UY', array('label' => $functions->T('contract_uy') ,'size' => 15));
        $this->addElement('note', 'CONTRACT_INSURED', array('label' => $functions->T('contract_insured') ,'size' => 15));
        $this->addElement('note', 'CONTRACT_UNDERWRITER', array('label' => $functions->T('contract_underwriter') ,'size' => 15));
        $this->addElement('note', 'VALUTA', array('label' => $functions->T('valuta') ,'size' => 15));
        $this->addElement('note', 'CONTRACT_NUMBER', array('label' => $functions->T('contract_number') ,'size' => 15));
        $this->addElement('note', 'CONTRACT_INCEPTIONDATE', array('label' => $functions->T('contract_inceptiondate') ,'size' => 15));
        $this->addElement('note', 'CONTRACT_LINEOFBUSINESS', array('label' => $functions->T('contract_linofbusiness') ,'size' => 15));
        $this->addElement('note', 'LEDGER_ACCOUNT', array('label' => $functions->T('contract_ledger_account') ,'size' => 15));
        $this->addElement('note', 'INVOICE_DATE',array('label' => $functions->T('factuurdatum_c'),'size' => 15,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('note', 'INVOICE_FROMDATE',array('label' => $functions->T('invoice_fromdate'),'size' => 15,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('note', 'INVOICE_TODATE',array('label' => $functions->T('invoice_todate'),'size' => 15,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('note', 'START_DATE',array('label' => $functions->T('vervaldatum_c'),'size' => 15,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('note', 'END_DATE',array('label' => $functions->T('end_date_c'),'size' => 15,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('note', 'AMOUNT',array('label' => $functions->T('amount_c'),'size' => 15, 'disabled' => true, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('select', 'AUTO_CALCULATE',array('label' => $functions->T('auto_calculate_c'),'MultiOptions' => array("1" => $functions->T('yes_c'), "0" => $functions->T('no_c')),'disabled' => true,'OnChange' => "autoCalculate()"));
        $this->addElement('note', 'INTEREST',array('label' => $functions->T('interest_c'),'size' => 15, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('note', 'COSTS',array('label' => $functions->T('costs_c'),'size' => 15, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('note', 'INTEREST_PERCENT',array('label' => $functions->T('Interest_percent_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('note', 'COST_PERCENT',array('label' => $functions->T('Cost_percent_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('note', 'INTEREST_MINIMUM',array('label' => $functions->T('Interest_minimum_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('note', 'COST_MINIMUM',array('label' => $functions->T('Cost_minimum_c'),'size' => 5, 'validators'=> array($helper->getFloatValidator())));
        $this->addElement('select', 'DISPUTE',array('label' => $functions->T('dispute_c'),'MultiOptions' => array("1" =>$functions->T('yes_c'), "0" => $functions->T('no_c')) , 'OnChange' => "dispute()"));
        $this->addElement('textarea', 'DISPUTE_COMMENT', array('label' => $functions->T('dispute_comment_c'), 'rows' => 5, 'cols' => 45));

        $disputeStatusses = $this->getSettingsMultiOptions($functions, "setting_dispute_statusses");
        $disputeAssignees = $this->getSettingsMultiOptions($functions, "setting_dispute_assignees");

        $this->addElement('select', 'DISPUTE_ASSIGNEE', array('label' => $functions->T('dispute_assignee_c'), 'MultiOptions' => $disputeAssignees));
        $this->addElement('select', 'DISPUTE_STATUS', array('label' => $functions->T('dispute_status_c'), 'MultiOptions' => $disputeStatusses));
        $this->addElement('text', 'DISPUTE_DATE',array('label' => $functions->T('dispute_date_c'),'size' => 15,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'DISPUTE_DUEDATE',array('label' => $functions->T('dispute_duedate_c'),'size' => 15, 'false' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'DISPUTE_ENDED_DATE',array('label' => $functions->T('dispute_ended_date_c'),'size' => 15,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'DISPUTE_AMOUNT',array('label' => $functions->T('dispute_amount_c'),'size' => 15,'validators'=>array ('validators'=> array($helper->getFloatValidator()))));
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

