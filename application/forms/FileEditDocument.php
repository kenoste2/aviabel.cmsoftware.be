<?php

class Application_Form_FileEditDocument extends Zend_Form {

    private $fileId;

    public function __construct($fileId, $options = null) {
        $this->fileId = $fileId;
        parent::__construct($options);
    }

    public function init() {
        $functions = new Application_Model_CommonFunctions();
        $referenceObj = new Application_Model_FilesReferences();

        $references = $referenceObj->getAllReferencesByFileIdAsArray($this->fileId);
        $this->addElement('text', 'DESCRIPTION', array('label' => $functions->T('description_c'), 'size' => 40));
        $this->addElement('select', 'REFERENCE_ID', array('label' => $functions->T('invoice_c'),
            'required' => false,
            'MultiOptions' => $functions->db2array($references)
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

}

