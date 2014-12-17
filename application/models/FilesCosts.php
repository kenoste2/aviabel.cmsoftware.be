<?php

require_once 'application/models/Base.php';

class Application_Model_FilesCosts extends Application_Model_Base {

    public function delete($id) {
        $sql = "DELETE FROM FILES\$FILE_COSTS WHERE RECORD_ID = {$id}";
        $this->db->query($sql);
    }

    public function save($data, $where) {
        $this->saveData('FILES\$FILE_COSTS', $data, $where);
    }

    public function add($data) {
        $data['CREATION_DATE'] = date("Y-m-d");
        if ($data['AMOUNT_CLIENT'] == '') {
            $data['AMOUNT_CLIENT'] = $data['AMOUNT'];
        }
        $this->addData('FILES$FILE_COSTS', $data);
        return true;
    }

    public function getCostsField() {
        $sql = "select COST_ID,(DESCRIPTION||' ('||AMOUNT||')') AS DESCRIPTION from FILES\$COSTS order by DESCRIPTION";
        $results = $this->db->get_results($sql,ARRAY_N);
        return $this->functions->db2array($results,false);
    }

}

?>
