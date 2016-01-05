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
            'MultiOptions' => $functions->db2array($collectors),
            'class' => 'form-control'
        ));

        $underwriters = $db->get_results("select CONTRACT_UNDERWRITER AS ID, CONTRACT_UNDERWRITER  from FILES\$REFERENCES group by CONTRACT_UNDERWRITER order by CONTRACT_UNDERWRITER", ARRAY_N);

        $this->addElement('select', 'CONTRACT_UNDERWRITER', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($underwriters),
            'class' => 'form-control'
        ));

        $lob = $db->get_results("select CONTRACT_LINEOFBUSINESS AS ID, CONTRACT_LINEOFBUSINESS  from FILES\$REFERENCES group by CONTRACT_LINEOFBUSINESS order by CONTRACT_LINEOFBUSINESS", ARRAY_N);

        $this->addElement('select', 'CONTRACT_LINEOFBUSINESS', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($lob),
            'class' => 'form-control'
        ));



        $this->addDisplayGroup(array('CONTRACT_UNDERWRITER', 'CONTRACT_LINEOFBUSINESS' ), 'group1');
        $this->getDisplayGroup('group1')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:left;padding:10px;'))
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
        $this->addDisplayGroup(array('COLLECTOR_ID','submit'), 'group2');
        $this->getDisplayGroup('group2')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:right;padding:10px;'))
        ));

    }
}

