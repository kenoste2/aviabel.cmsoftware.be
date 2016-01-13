<?php

class Application_Form_SendEmail extends Zend_Form {

    private $_fileId;

    function __construct($fileId){
        $this->_fileId = $fileId;
        parent::__construct();
    }

    function init() {
        $functions = new Application_Model_CommonFunctions();
        //$this->addElement('text', 'TO', array('label' => $functions->T('email_to_c'), 'size' => 60, 'required' => true));

        $allowedMailsObj = new Application_Model_AllowedMails();

        $emails = $allowedMailsObj->getFileAllowedMails($this->_fileId);
        $this->addElement('select', 'TO', array(
            'MultiOptions' => $emails,
            'label' => $functions->T('email_to_c'),
            'required' => false,
        ));


        $clientObj = new Application_Model_Clients();
        $fileObj = new Application_Model_File();
        $file = $fileObj->getFileData($this->_fileId);
        $client = $clientObj->getClientIdById($file->CLIENT_ID);
        $fromEmail = $client->E_MAIL ? $client->E_MAIL : $functions->T('email_unknown_c');

        $clientEmailTrans = $functions->T('client_email_c');
        $clientEmailLabel = "{$clientEmailTrans} ({$fromEmail})";

        $userObj = new Application_Model_Users();
        $user = $userObj->getLoggedInUser();
        $fromEmail = $user->E_MAIL ? $user->E_MAIL : $functions->T('email_unknown_c');

        $userEmailTrans = $functions->T('user_email_c');
        $userEmailLabel = "{$userEmailTrans} ({$fromEmail})";

        $this->addElement('radio', 'FROM', array('label' => $functions->T('email_from_c'), 'multiOptions' => array(
            'CLIENT' => $clientEmailLabel,
            'USER' => $userEmailLabel,
            //'CUSTOM' => $functions->T('custom_email_c'),
        )));

        $this->addElement('text', 'CUSTOM_FROM', array('label' => $functions->T('email_custom_from_c'), 'size' => 50, 'required' => false));
        $this->addElement('text', 'SUBJECT', array('label' => $functions->T('subject_c'), 'size' => 60, 'required' => true));
        $this->addElement('textarea', 'CONTENT', array('label' => $functions->T('content_c'), 'size' => 15, 'rows' => 30, 'cols' => 110, 'required' => true));
        $this->addElement('submit', 'SUBMIT', array(
            'ignore' => true,
            'label' => $functions->T('send_c'),
        ));

        $filesDocumentsObj = new Application_Model_FilesDocuments();
        $fileDocuments = $filesDocumentsObj->getDocumentsFromFile($this->_fileId);
        $documents = array();
        if(count($fileDocuments)) {
            foreach($fileDocuments as $fileDocument) {
                $fileDescription = $fileDocument->FILENAME;
                if($fileDocument->DESCRIPTION) {
                    $fileDescription = "{$fileDocument->DESCRIPTION} ({$fileDocument->FILENAME})";
                }
                $documents[$fileDocument->FILE_DOCUMENTS_ID] = $fileDescription;
            }
            $this->addElement('multiCheckbox', 'DOCUMENTS', array('label' => $functions->T('attachments_c'), 'MultiOptions' => $documents));
        }

        $fileActionsObj = new Application_Model_FilesActions();
        $actionDocuments = $fileActionsObj->getActionsWithDocumentsByFileId($this->_fileId);
        $documents = array();
        if(count($actionDocuments)) {
            foreach($actionDocuments as $actionDocument) {
                $dateStr = $actionDocument->ACTION_DATE;
                $fileDescription = "{$dateStr} {$actionDocument->ACTION_CODE}: {$actionDocument->ACTION_DESCRIPTION}";
                $documents[$actionDocument->FILE_ACTION_ID] = $fileDescription;
            }
            $this->addElement('multiCheckbox', 'ACTION_DOCUMENTS', array('label' => $functions->T('attachments_c'), 'MultiOptions' => $documents));
        }
    }

    public function addTripleAEmailDecorator($fromField)
    {
        //NOTE: this is a lot of code to generate the following right behind the field:
        //      <a class=\"ui-icon ui-icon-zoomin inline-icon\" style=\"display: inline-block;\" href=\"{$this->_location}/client-detail/view/clientId/{$this->file->CLIENT_ID}\"></a>";
        //NOTE: check out this article for more on decorators: http://devzone.zend.com/1240/decorators-with-zend_form/
        $decorators = $fromField->getDecorators();

        //NOTE: just inserting our custom-decorator in the array won't get it at the right position (for weird PHP-internal reasons) so we need to create a new array of decorators.
        $newDecorators = array();
        $i = 0;
        foreach ($decorators as $decorator) {
            $newDecorators [] = $decorator;
            if ($i == 1) {
                //NOTE: insert the custom decorator at the 2nd position in the array.
                $newDecorators [] = array(array("tripleAEmail" => "HtmlTag"),
                    array('tag' => 'span',
                        'placement' => 'append',
                        'innerHtml' => '@aaa.be'
                    )
                );
            }
            $i++;
        }
        $fromField->setDecorators($newDecorators);
    }
}

