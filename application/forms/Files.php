<?php

class Application_Form_Files extends Zend_Form
{

    public function init()
    {

        global $db;
        $functions = new Application_Model_CommonFunctions();


        $this->setMethod('post');

        $this->addElement(
            'text', 'debtor', array(
            'required' => false,
        ));

        $this->addElement('text', 'client', array(
            'required' => false,
        ));

        $this->addElement('text', 'client_reference', array(
            'required' => false,
        ));

        $this->addElement('text', 'from_file_nr', array(
            'required' => false,
        ));
        $this->addElement('text', 'to_file_nr', array(
            'required' => false,
        ));

        $this->addElement('text', 'invoice', array(
            'required' => false,
        ));

        $collectors = $db->get_results("select COLLECTOR_ID,(CODE || ' - ' || NAME  ) AS NAME from SYSTEM\$COLLECTORS where ACTIF='Y' AND COALESCE(EXTERN, 0) = 0 order by NAME", ARRAY_N);

        $this->addElement('select', 'collector', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($collectors),
        ));

        $externalCollectors = $db->get_results("select COLLECTOR_ID,(CODE || ' - ' || NAME  ) AS NAME from SYSTEM\$COLLECTORS where ACTIF='Y' AND EXTERN = 1 order by NAME", ARRAY_N);

        $this->addElement('select', 'external_collector', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($externalCollectors),
        ));

        $stateCodes = $db->get_results("select STATE_ID,CODE from FILES\$STATES where ACTIEF='1' order by CODE", ARRAY_N);

        $this->addElement('select', 'state_id', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($stateCodes),
        ));

        $trainTypes = $db->get_results("select TRAIN_TYPE, TRAIN_TYPE AS DISPLAY from TRAIN group by TRAIN_TYPE order by TRAIN_TYPE", ARRAY_N);

        $this->addElement('select', 'train_id', array(
            'required' => false,
            'MultiOptions' => $functions->db2array($trainTypes),
        ));

        $functions = new Application_Model_CommonFunctions();

        $this->addElement('select', 'extra_field', array(
            'MultiOptions' => array(
                'AMOUNT' => $functions->T('amount_c'),
                'DATE_CLOSED' => $functions->T('close_date_c'),
                'CLOSE_STATE_CODE' => $functions->T('close_state_c'),
                'CREATION_DATE' => $functions->T('creationdate_c'),
                'LAST_ACTION_DATE' => $functions->T('last_action_date_c'),
                'PAYABLE' => $functions->T('payable_c'),
                'STATE_CODE' => $functions->T('state_code_c'),
                'TOTAL' => $functions->T('total_c'),
                'DEBTOR_SCORE' => $functions->T('nr_of_stars_c')
            )
        ));

        $this->addElement('select', 'extra_compare', array(
            'MultiOptions' => array(
                '=' => $functions->T('equal_c'),
                '>=' => $functions->T('greater_then_c'),
                '<=' => $functions->T('smaller_then_c'),
                'containing' => $functions->T('containing_c'),
            )
        ));


        $this->addElement('text', 'extra_text', array());
        $this->addElement('checkbox', 'closed_files', array('value' => 'Y'));


        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
    }

}

