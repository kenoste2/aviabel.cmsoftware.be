<?php

class Application_Form_Query extends Zend_Form {

    public function init() {
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('textarea', 'QUERY', array('label' => 'query', 'size'=> 30, 'required' => true, 'rows' => 10, 'cols' => 60));
        $this->addElement('text', 'VERIFICATION', array('label' => 'verification', 'size'=> 30 , 'required' => true));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}
