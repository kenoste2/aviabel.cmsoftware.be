<?php

class Application_Form_SearchReport extends Zend_Form
{

    public function init()
    {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();


        $collectors = $db->get_results("select COLLECTOR_ID,(CODE || ' - ' || NAME  ) AS NAME from SYSTEM\$COLLECTORS where ACTIF='Y' order by NAME", ARRAY_N);

        $this->addElement('select', 'COLLECTOR_ID', array(
            'required' => false,
            'MultiOptions' => array('' => 'Select caseworker')+$functions->db2array($collectors),
            'class' => 'form-control'
        ));

        $lob = $db->get_results("select CONTRACT_LINEOFBUSINESS AS ID, CONTRACT_LINEOFBUSINESS  from FILES\$REFERENCES group by CONTRACT_LINEOFBUSINESS order by CONTRACT_LINEOFBUSINESS", ARRAY_N);

        $this->addElement('select', 'CONTRACT_LINEOFBUSINESS', array(
            'required' => false,
            'MultiOptions' => array('' => 'Select line of business')+$functions->db2array($lob),
            'class' => 'form-control'
        ));


        $underwriters = $db->get_results("select CONTRACT_UNDERWRITER AS ID, CONTRACT_UNDERWRITER  from FILES\$REFERENCES group by CONTRACT_UNDERWRITER order by CONTRACT_UNDERWRITER", ARRAY_N);

        $this->addElement('select', 'CONTRACT_UNDERWRITER', array(
            'required' => false,
            'MultiOptions' => array('' => 'Select underwriter')+$functions->db2array($underwriters),
            'class' => 'form-control'
        ));

        $this->addElement('select', 'GROUP_BY', array(
            'required' => true,
            'MultiOptions' => array(
                                    'DEFAULT' => 'Group by',
                                    'CASEWORKERS' => 'Caseworker',
                                    'LINEOBUSINESS' => 'Line of business',
                                    'UNDERWRITERS' => 'Underwriter'),
            'class' => 'form-control'
        ));


        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));


    }
}

