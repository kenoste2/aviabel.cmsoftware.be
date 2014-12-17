<?php

require_once 'application/controllers/BaseController.php';

class BaseFileController extends BaseController {

    public $fileId;
    protected $file;
    protected $file2;
    protected $index;

    public function init() {

        parent::init();
        
        $this->_helper->_layout->setLayout('file-layout');
        $session = new Zend_Session_Namespace('FILES');


        if ($this->getParam("fileId")) {
            $this->fileId = $this->getParam("fileId");
            $session->fileId = $this->getParam("fileId");
            $session->fileList = false;
            $this->view->indexes['prevIndex'] = false;
            $this->view->indexes['nextIndex'] = false;
            $this->view->index = 0;
        } else {
            $indexes = $this->_getNextPrevCurrent();
            $this->fileId = $indexes['currentFileId'];
            $this->view->indexes = $indexes;
            $this->view->index = $this->getParam("index");
        }


        if (empty($this->fileId) && !empty($session->fileId)) {
            $this->fileId = $session->fileId;
        }


        $this->loadFile();

        $this->view->headerTitle = "{$this->file->DEBTOR_NAME} - {$this->file->FILE_NR}";

        $this->view->showFileTodos = $this->hasAccess('showFileTodos');
        $this->view->showFileRemarks = $this->hasAccess('showFileRemarks');
        $this->view->showFileCosts = $this->hasAccess('showFileCosts');
    }

    protected function _getNextPrevCurrent() {
        $session = new Zend_Session_Namespace('FILES');
        $index = $this->getParam("index");
        
        switch ($index) {
            case 0:
                $next = (key_exists(1, $session->fileList)) ? 1 : false;
                $prev = false;
                break;
            case ($index > 0):
                $next = (key_exists($index + 1, $session->fileList)) ? $index + 1 : false;
                $prev = (key_exists($index - 1, $session->fileList)) ? $index - 1 : false;
                break;
        }

        $indexes = array(
            'currentFileId' => $session->fileList[$index]['FILE_ID'],
            'nextIndex' => $next,
            'prevIndex' => $prev,
        );

        return $indexes;
    }
    
    protected function loadFile() {
        $this->file = $this->db->get_row("SELECT * FROM FILES\$FILES_ALL_INFO 
            WHERE FILE_ID = {$this->fileId}");
        $this->file2 = $this->db->get_row("SELECT * FROM FILES\$FILES 
            WHERE FILE_ID = {$this->fileId}");
        $this->view->file = $this->file;
        $this->view->file2 = $this->file2;
    }
    

}

