<?php

class Application_Form_ImportCsv extends Zend_Form {

    public function init($fileId = null) {

        $functions = new Application_Model_CommonFunctions();
        $this->addElement('file', 'csvfile', array('label' => $functions->T('bestand_c'), 'required' => true));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('upload_c'),
        ));
    }
}
