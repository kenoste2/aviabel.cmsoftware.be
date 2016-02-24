<?php

class Application_Form_bInformation extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'BNAME', array('label'=> $functions->T('name_c'),'size' => 50, 'required' => false));
        $this->addElement('text', 'BVATNR', array('label'=> $functions->T('vatnr_c'),'size' => 50));
        $countries = $db->get_results("select COUNTRY_ID,DESCRIPTION from SUPPORT\$COUNTRIES  order by DESCRIPTION", ARRAY_N);
        array_unshift($countries,array('0' =>4,1 => 'BELGIUM'));
        $this->addElement('select', 'BCOUNTRY_ID', array('label'=> $functions->T('country_c'),'MultiOptions' => $functions->db2array($countries,false)));

        $this->addElement('button', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
            'class' => 'submit',
            'onClick' => 'SearchBinformation()',
        ));
    }

}

