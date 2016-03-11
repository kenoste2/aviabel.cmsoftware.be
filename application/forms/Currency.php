<?php

class Application_Form_Currency extends Zend_Form {

    public function init()
    {
        global $db;
        $functions = new Application_Model_CommonFunctions();

        $this->setMethod('post');

        $this->addElement('text', 'CREATION_DATE',
            array(  'label' => $functions->T('date_currencyrate_c'),
                    'class' => 'hasDatePicker',
                    'data-fieldtype' => 'datepicker',
                    'validators'=> array (array('date', false, array('yyyy-MM-dd')))));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
    }

}
