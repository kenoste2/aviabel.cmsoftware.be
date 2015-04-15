<?php

class Application_Form_MailOverview extends Zend_Form {

    public function init() {

        $functions = new Application_Model_CommonFunctions();
        $now = new DateTime();
        $this->addElement('text', 'FROM_DATE',array('label' => $functions->T('date_from_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))), 'value' => $now->format('d/m/Y')));
        $this->addElement('text', 'TO_DATE',array('label' => $functions->T('date_till_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))), 'value' => $now->format('d/m/Y')));
        $this->addElement('submit', 'submit', array(
                'ignore' => true,
                'label' => $functions->T('search_c'),
            ));
    }
}