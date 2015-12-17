<?php

class Application_Form_FileGeneral extends Zend_Form
{

    protected $_isClient;
    protected $_isCollector;

    /**
     * @param mixed $isClient
     * @return $this
     */
    public function setIsClient($isClient)
    {
        $this->_isClient = $isClient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsClient()
    {
        return $this->_isClient;
    }

    /**
     * @param mixed $isClient
     * @return $this
     */
    public function setIsCollector($isCollector)
    {
        $this->_isCollector = $isCollector;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsCollector()
    {
        return $this->_isCollector;
    }

    public function init()
    {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');

        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();
        $this->addElement('hidden', 'GENERALFORM', array("value" => 1));
        $this->addElement('note', 'CLIENT_NAME', array('label' => $functions->T('client_c'), 'size' => 15, 'disabled' => true));
        //$this->addElement('note', 'CREATION_DATE', array('label' => $functions->T('Created_c'), 'size' => 15, 'disabled' => true));
        $stateCodes = $db->get_results("select STATE_ID,CODE from FILES\$STATES where ACTIEF='1' order by CODE", ARRAY_N);
        $this->addElement('select', 'STATE_ID', array(
            'label' => $functions->T('state_c'),
            'required' => false,
            'disabled' => true,
            'MultiOptions' => $functions->db2array($stateCodes),
        ));


        $this->addElement('note', 'NEXT_ACTION', array('label' => $functions->T('next_action_c'), 'size' => 15, 'disabled' => true));
        $this->addElement('text', 'REFERENCE', array('label' => $functions->T('referenceclient_c'), 'size' => 25, 'disabled' => true, 'required' => false));
        $this->addElement('text', 'PARTNER', array('label' => $functions->T('partner_c'), 'size' => 25, 'disabled' => true, 'required' => true));
        //$closeStates = $db->get_results("select CLOSE_STATE_ID,DESCRIPTION from FILES\$CLOSE_STATES order by DESCRIPTION", ARRAY_N);
        //$this->addElement('select', 'CLOSE_STATE_ID', array(
        //    'label' => $functions->T('close_state_c'),
        //    'MultiOptions' => $functions->db2array($closeStates, false),
        //));
        $helper = new Application_Form_FormHelper();

        //$this->addElement('text', 'VERJARING', array('label' => $functions->T('verjaring_c'), 'size' => 15));
        //$this->addElement('text', 'INCASSOKOST', array('label' => $functions->T('incasso_kost_c'), 'size' => 15, 'required' => true, 'validators' => array($helper->getFloatValidator())));

        $collectorObj = new Application_Model_Collectors();
        $this->addElement('radio', 'COLLECTOR_ID', array(
            'label' => $functions->T('collector_c'),
            'required' => false,
            'MultiOptions' => $functions->db2array($collectorObj->getCollectorsForSelect(), false),
            'separator' => ' ',
        ));

        /*
         $this->addElement('radio', 'EXTERNAL_COLLECTOR_ID', array(
            'label' => $functions->T('external_collector_c'),
            'required' => false,
            'MultiOptions' => $functions->db2array($collectorObj->getExternalCollectorsForSelect(), false),
            'separator' => ' ',
        ));
        */
        $confirm = array(1 => $functions->T('yes_c'), 0 => $functions->T('no_c'));
        //$this->addElement('radio', 'COLLECTOR_VISIBLE', array(
        //    'label' => $functions->T('visible_for_collector_c'),
        //    'required' => false,
        //    'MultiOptions' => $confirm,
        //    'separator' => ' ',
        //));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));


    }

    public function checkFields()
    {
        if ($this->_isClient or $this->_isCollector) {
            $this->removeElement('COLLECTOR_ID');
            $this->removeElement('COLLECTOR_VISIBLE');
            //$this->removeElement('INCASSOKOST');
            //$this->removeElement('VERJARING');
            //$this->getElement("CLOSE_STATE_ID")->setAttrib('disabled', 'disabled');
            $this->removeElement('submit');
        } else {
            $this->getElement("COLLECTOR_ID")->setAttrib('disabled', 'disabled');
        }
    }

    public function disablePartner()
    {
        $this->removeElement('PARTNER');
    }



}

