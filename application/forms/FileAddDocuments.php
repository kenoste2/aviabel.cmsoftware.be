<?php

class Application_Form_FileAddDocuments extends Zend_Form {

    public function init($fileId = false) {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();


        $confirm = array(1 => $functions->T('yes_c'), 0 => $functions->T('no_c'));
        $this->addElement('radio', 'VISIBLE', array(
            'label' => $functions->T('visible_c'),
            'required' => false,
            'MultiOptions' => $confirm,
        ));

        //$this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('description_c'), 'required' => false, 'size' => 50));
        $this->addElement('file', 'userfile1', array('label' => $functions->T('bestand_c') . "1", 'required' => true));
        $this->addElement('file', 'userfile2', array('label' => $functions->T('bestand_c') . "2", 'required' => false));
        $this->addElement('file', 'userfile3', array('label' => $functions->T('bestand_c') . "3", 'required' => false));
        $this->addElement('file', 'userfile4', array('label' => $functions->T('bestand_c') . "4", 'required' => false));
        $this->addElement('file', 'userfile5', array('label' => $functions->T('bestand_c') . "5", 'required' => false));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('upload_c'),
        ));
    }

}

