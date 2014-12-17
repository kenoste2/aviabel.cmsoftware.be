<?php
class Application_Form_Import extends Zend_Form
{
	public function init()
	{
        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('file', 'userfile', array('label' => $functions->T('Select_document_c'), 'required' => true));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
	}
}