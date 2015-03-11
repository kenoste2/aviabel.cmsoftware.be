<?php

require_once 'application/models/Base.php';

class Application_Model_FilesDocuments extends Application_Model_Base {

    public function delete($id) {
        global $config;

        $document = $this->db->get_row("SELECT * FROM FILE_DOCUMENTS WHERE FILE_DOCUMENTS_ID={$id}");

        $file_nr = $this->db->get_var("SELECT FILE_NR FROM FILES\$FILES WHERE FILE_ID={$document->FILE_ID}");

        $filelink = $config->rootFileDocuments . '/' . $document->FILENAME;

        if (file_exists($filelink)) {
            unlink($filelink);
        }

        $sql = "DELETE FROM FILE_DOCUMENTS WHERE FILE_DOCUMENTS_ID = {$id}";
        $this->db->query($sql);
    }

    public function save($data, $where) {
        $this->saveData('FILES$DOCUMENTS', $data, $where);
    }

    public function add($fileId, $file, $description, $visible) {
        global $config;

        $sql = "SELECT MAX(FILE_DOCUMENTS_ID) FROM FILE_DOCUMENTS";
        $last_id = $this->db->get_var($sql);
        $last_id++;
        $file_nr = $this->db->get_var("SELECT FILE_NR FROM FILES\$FILES WHERE FILE_ID=$fileId");

        $info = $file->getFileInfo();

        $keys = array_keys($info);
        $userfile = $keys[0];

        $extension = '';
        if (file_exists($info[$userfile]['tmp_name']))
            $originalFilename = $file->getFileName();

        if (!empty($originalFilename)) {
            $matches = array();
            if (preg_match('/^(.*?)\.(.*)$/', $originalFilename, &$matches)) {
                $originalFilename = $matches[1];
                $extension = $matches[2];
            }


            $rand = rand(0,99999);

            $filename = $config->rootFileDocuments . '/' . $file_nr . '_' . $last_id . $rand . '.' . $extension;
            $filenameUrl = $file_nr . '_' . $last_id . $rand . '.' . $extension;

            if (copy($info[$userfile]['tmp_name'], $filename)) {
                unlink($info[$userfile]['tmp_name']);

                if (empty($description)) {
                    $description = $info[$userfile]['name'];
                }

                $data = array(
                    'FILE_DOCUMENTS_ID' => $last_id,
                    'FILE_ID' => $fileId,
                    'DESCRIPTION' => $description,
                    'VISIBLE' => $visible,
                    'FILENAME' => $filenameUrl,
                    'CREATED' => date("Y-m-d"),
                    'CREATEDBY' => $this->online_user,
                );

                $this->addData('FILE_DOCUMENTS', $data);
            }
        }
    }

    public function getDocumentsFromFile($fileId) {
        $sql = "SELECT * FROM FILE_DOCUMENTS WHERE FILE_ID={$fileId} ORDER BY FILENAME";
        $results = $this->db->get_results($sql);
        return $results;
    }

    public function getDocumentsByIds($documentIds) {
        if(count($documentIds) <= 0) {
            return array();
        }

        $documentIdsStr = implode(',', $documentIds);

        $escDocumentIdsStr = $this->db->escape($documentIdsStr);
        $sql = "SELECT * FROM FILE_DOCUMENTS WHERE FILE_DOCUMENTS_ID IN ({$escDocumentIdsStr})";
        return $this->db->get_results($sql);
    }

    public function getNextId()
    {
        $sql = "SELECT MAX(FILE_DOCUMENTS_ID) FROM FILE_DOCUMENTS";
        $id = $this->db->get_var($sql);
        if (empty($id)) {
            $id = 0;
        }
        $id++;
        return $id;
    }

    /**
     * @param $originalFilename
     * @return mixed
     */
    private function getExtension($originalFilename)
    {

        $extension = "";

        $matches = array();
        if (preg_match('/^(.*?)\.(.*)$/', $originalFilename, $matches)) {
            $originalFilename = $matches[1];
            $extension = $matches[2];
            return $extension;
        }
        return $extension;
    }
}

?>
