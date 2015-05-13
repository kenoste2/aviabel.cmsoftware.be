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

        $indexes = $this->_getNextPrevCurrent();
        $this->fileId = $indexes['currentFileId'];
        $this->view->indexes = $indexes;

        $this->loadFile();
        if(!$this->file) {
            $this->_redirect('error/noaccess');
        }

        $this->view->headerTitle = "{$this->file->DEBTOR_NAME} - {$this->file->FILE_NR}";

        $this->view->showFileTodos = $this->hasAccess('showFileTodos');
        $this->view->showFileRemarks = $this->hasAccess('showFileRemarks');
        $this->view->showFileCosts = $this->hasAccess('showFileCosts');
    }

    protected function _getNextPrevCurrent() {
        $session = new Zend_Session_Namespace('FILES');
        $fileId = $this->getParam("fileId");

        $next = false;
        $prev = true;

        $index = -1;

        if($session->fileList && count($session->fileList) > 0) {

            foreach($session->fileList as $item) {
                $index++;
                if($item['FILE_ID'] == $fileId) {
                    break;
                }
            }

            if($index < count($session->fileList) ) {
                $next = $session->fileList[$index + 1];
            }

            if($index > 0 && $index <= count($session->fileList)) {
                $prev = $session->fileList[$index - 1];
            }
        }

        $indexes = array(
            'currentFileId' => $fileId,
            'nextIndex' => $next ? $next['FILE_ID'] : false,
            'prevIndex' => $prev ? $prev['FILE_ID'] : false,
        );

        $this->view->fileId = $fileId;
        return $indexes;
    }
    
    protected function loadFile() {
        $filesObj = new Application_Model_Files();
        $query_extra = $filesObj->extraWhereClauseForUserRights($this->auth);
        $this->file = $this->db->get_row("SELECT * FROM FILES\$FILES_ALL_INFO A
            WHERE A.FILE_ID = {$this->fileId} {$query_extra}");
        $this->file2 = $this->db->get_row("SELECT * FROM FILES\$FILES A
            WHERE A.FILE_ID = {$this->fileId} {$query_extra}");
        $this->view->file = $this->file;
        $this->view->file2 = $this->file2;
    }
    

}

