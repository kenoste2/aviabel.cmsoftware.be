<?php

class Application_Form_MailOverview extends Zend_Form {

    public function init() {

        $functions = new Application_Model_CommonFunctions();
        $this->addElement('text', 'FROM_DATE',array('label' => $functions->T('mail_from_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy')))));
        $this->addElement('text', 'TO_DATE',array('label' => $functions->T('mail_to_c'),'size' => 15, 'required' => true,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('submit', 'submit', array(
                'ignore' => true,
                'label' => $functions->T('search_c'),
            ));
    }
}