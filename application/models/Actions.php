<?php

require_once 'application/models/Base.php';

class Application_Model_Actions extends Application_Model_Base
{
    public function getSettingActions()
    {
        return $this->db->get_results("select * from FILES\$ACTIONS where ACTIEF='1' order by CODE");
    }

    public function getSetting($action_id)
    {
        return $this->db->get_row("SELECT * FROM FILES\$ACTIONS WHERE ACTION_ID = " . $action_id);
    }

    public function add($data)
    {
        $data['ACTIEF'] = "1";
        if(empty($data['FILE_STATE_ID'])) {
            $data['FILE_STATE_ID'] = 0;
        }
        if(empty($data['COST_ID'])) {
            unset($data['COST_ID']);
        }
        return $this->addData("FILES\$ACTIONS", $data);
    }

    public function save($data, $where)
    {
        if(empty($data['FILE_STATE_ID'])) {
            $data['FILE_STATE_ID'] = 0;
        }
        if(empty($data['COST_ID'])) {
            $data['COST_ID'] = null;
        }
        return $this->saveData("FILES\$ACTIONS", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("update FILES\$ACTIONS set ACTIEF='0' where ACTION_ID='$id'");
    }

    public function getActionsForSelect()
    {
        return $this->db->get_results("select ACTION_ID, DESCRIPTION from FILES\$ACTIONS where ACTIEF='1' order by CODE", ARRAY_N);
    }

    public function getActionsForSelectWithCode()
    {
        return $this->db->get_results("select CODE, CODE AS CODE2 from FILES\$ACTIONS where ACTIEF='1' order by CODE", ARRAY_N);
    }

    public function getActionByCode($code)
    {
        return $this->db->get_row("select ACTION_ID, DESCRIPTION from FILES\$ACTIONS where CODE = '{$code}'", ARRAY_A);
    }

    public function getActionByStateId($stateId)
    {
        return $this->db->get_var("SELECT ACTION_ID FROM FILES\$ACTIONS WHERE FILE_STATE_ID = '{$stateId}'");
    }


}

?>
