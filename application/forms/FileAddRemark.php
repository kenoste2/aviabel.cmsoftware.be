<?php

class Application_Form_FileAddRemark extends Zend_Form {

    public function init($fileId = false) {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();
        
        $this->addElement('textarea', 'REMARK', array('label' => $functions->T('remark_c'), 'required' => true, 'rows' => 5, 'cols' => 50));
        $this->addElement('textarea', 'REMARK_CLIENT', array('label' => $functions->T('remark_c') . " " . $functions->T('client_c'), 'required' => true, 'rows' => 5, 'cols' => 50));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

