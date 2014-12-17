<?php

require_once 'application/models/Base.php';

class Application_Model_Closestates extends Application_Model_Base
{
    public function getSettingClosestates()
    {
        return $this->db->get_results("select * from FILES\$CLOSE_STATES");
    }

    public function getSetting($closestate_id)
    {
        return $this->db->get_row("SELECT * FROM FILES\$CLOSE_STATES WHERE CLOSE_STATE_ID = " . $closestate_id);
    }

    public function add($data)
    {
        $data['CLOSE_STATE_ID'] = $this->db->get_var("SELECT MAX(CLOSE_STATE_ID)+1 AS NEWID FROM FILES\$CLOSE_STATES");
        return $this->addData("FILES\$CLOSE_STATES", $data);
    }

    public function save($data, $where)
    {
        return $this->saveData("FILES\$CLOSE_STATES", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from FILES\$CLOSE_STATES where CLOSE_STATE_ID='$id'");
    }

    public function getStatesForSelect()
    {
        return $this->db->get_results("select CLOSE_STATE_ID, DESCRIPTION from FILES\$CLOSE_STATES", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        $results = $this->db->get_results("select count(*) from FILES\$FILES WHERE CLOSE_STATE_ID = '$id'");

        if ($results[0]->COUNT > 0) {
            return false;
        } else {
            return true;
        }
    }
}

?>
