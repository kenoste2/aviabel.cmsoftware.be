<?php

class Application_Form_ForwardEmail extends Application_Form_SendEmail {

    private $_emailId;
    private $_importedEmail;

    function __construct($emailId){
        $this->_emailId = $emailId;

        $importedEmailObj = new Application_Model_ImportedMails();
        $this->_importedEmail = $importedEmailObj->retrieveImportedMailById($emailId);
        parent::__construct($this->_importedEmail->FILE_ID);
    }

    function init() {
        parent::init();

        $functions = new Application_Model_CommonFunctions();

        $importedEmailObj = new Application_Model_ImportedMails();

        $this->SUBJECT->setValue("FWD: {$this->_importedEmail->MAIL_SUBJECT}");

        //NOTE: Zend_Form setValue does not accept strings containing long em-dashes.
        $mailBody = str_replace(chr(151),"--", $this->_importedEmail->MAIL_BODY);
        $this->CONTENT->setValue($mailBody);

        $emailDocuments = $importedEmailObj->retrieveAttachmentsByMailId($this->_importedEmail->IMPORTED_MAIL_ID);

        $documents = array();
        if(count($emailDocuments)) {
            foreach($emailDocuments as $emailDocument) {
                $documents[$emailDocument->IMPORTED_MAIL_ATTACHMENT_ID] = $emailDocument->ORIGINAL_FILENAME;
            }
            $this->addElement('multiCheckbox', 'EMAIL_DOCUMENTS', array(
                    'label' => $functions->T('attachments_c'),
                    'MultiOptions' => $documents,
                    'value' => array_keys($documents)));
        }
    }
}
