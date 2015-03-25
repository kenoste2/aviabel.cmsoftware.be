<?php

require_once 'application/controllers/BaseFileController.php';

class FileDocumentsController extends BaseFileController {

    public function viewAction() {
        
        global $config;

        if ($this->hasAccess('addDocuments')) {
            $this->view->addButton = "/file-documents/add/fileId/" . $this->fileId;
        }
        if ($this->hasAccess('deleteAllDocuments')) {
            $this->view->MayDelete = true;
        }

        $this->view->printButton = true;


        if ($this->getParam("delete")) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        if ($this->auth->online_rights != 4) {
            $extra_query = " AND f.VISIBLE = '1'";
        }
        $sql = "SELECT f.*, (SELECT FIRST 1 r.REFERENCE FROM FILES\$REFERENCES r
                        WHERE r.REFERENCE_ID = f.REFERENCE_ID) AS REFERENCE
                FROM FILE_DOCUMENTS f WHERE f.FILE_ID={$this->fileId} $extra_query ORDER BY f.FILENAME";
        $this->view->results = $this->db->get_results($sql);
        
        
        $this->view->locationFiles = $config->rootLocation.$config->MapFileDocuments;
        $this->view->online_user = $this->auth->online_user;
    }

    public function addAction() {
        $form = new Application_Form_FileAddDocuments();
        $obj = new Application_Model_FilesDocuments();


        if ($this->auth->online_rights == 5) {
            $form->removeElement('VISIBLE');
        }

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();

                if ($this->auth->online_rights == 5) {
                    $update['VISIBLE'] = 1;
                }

                for ($i = 1; $i <= 5; $i++) {
                    $fileName = 'userfile' . $i;
                    if (!empty($update[$fileName])) {
                        $obj->add($this->fileId, $form->$fileName, $update['DESCRIPTION'],$update['VISIBLE']);
                    }
                }
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
            $data['VISIBLE'] = 1;
        }
        // Populating form
        $form->populate($data);
        $this->view->form = $form;
    }

    public function editAction() {
        $form = new Application_Form_FileEditDocument($this->fileId);
        $fileDocumentObj = new Application_Model_FilesDocuments();

        $fileDocumentId = $this->getParam("fileDocumentId");

        $fileDocument = $fileDocumentObj->getById($fileDocumentId);

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $escFileDocumentId = $this->db->escape($fileDocumentId);
                $fileDocumentObj->save(array(
                    "REFERENCE_ID" => $data["REFERENCE_ID"] && $data["REFERENCE_ID"] != '' ? $data["REFERENCE_ID"] : null,
                    "DESCRIPTION" => $data["DESCRIPTION"] && $data["DESCRIPTION"] != '' ? $data["DESCRIPTION"] : null,
                    ), "FILE_DOCUMENTS_ID = {$escFileDocumentId}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $form->populate(array("REFERENCE_ID" => $fileDocument->REFERENCE_ID, "DESCRIPTION" => $fileDocument->DESCRIPTION));
            $data = array();
        }

        // Populating form
        $form->populate($data);

        $this->view->fileName = $fileDocument->FILENAME;
        $this->view->form = $form;
    }

    private function delete($id) {
        $Obj = new Application_Model_FilesDocuments();
        $Obj->delete($id);
    }

}

