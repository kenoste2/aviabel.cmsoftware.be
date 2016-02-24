<?php

class Application_Form_AddClient extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'client_name', array('label'=> $functions->T('client_c'),'size' => 50, 'required' => true));
        $this->addElement('hidden', 'client_id',array('required' => true));
        $this->addElement('text', 'client_reference',array('label'=> $functions->T('client_number_c'),'description' => $functions->T('known_by_client_c'),'size' => 50, 'required' => true, 'maxlength' => 200));
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

