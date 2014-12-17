<?php

require_once 'application/models/Base.php';

class Application_Model_FilesRemarks extends Application_Model_Base {

    public function delete($id) {
        $sql = "DELETE FROM FILES\$REMARKS WHERE REMARK_ID = {$id}";
        $this->db->query($sql);
    }

    public function save($data, $where) {
        $this->saveData('FILES$REMARKS', $data, $where);
    }

    public function add($data) {
        $data['CREATION_DATE'] =  date("Y-m-d");
        $data['CREATION_USER'] = $this->online_user;
        $this->addData('FILES$REMARKS', $data);
        return true;
    }

}

?>
