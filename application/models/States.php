<?php

require_once 'application/models/Base.php';

class Application_Model_States extends Application_Model_Base
{
    public function getSettingStates()
    {
        return $this->db->get_results("select * from FILES\$STATES where ACTIEF='1' order by CODE");
    }

    public function getSetting($state_id)
    {
        return $this->db->get_row("SELECT * FROM FILES\$STATES WHERE STATE_ID = " . $state_id);
    }

    public function add($data)
    {
        $data['ACTIEF'] = "1";
        $data['DELETEPOSSIBLE'] = "1";

        return $this->addData("FILES\$STATES", $data);
    }

    public function save($data, $where)
    {
        return $this->saveData("FILES\$STATES", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("update FILES\$STATES set ACTIEF='0' where STATE_ID='$id'");
    }

    public function getStatesForSelect()
    {
        return $this->db->get_results("select STATE_ID, DESCRIPTION from FILES\$STATES where ACTIEF='1' order by CODE", ARRAY_N);
    }

    public function getStatesForSelectByCode()
    {
        return $this->db->get_results("select CODE, CODE as CODE2 from FILES\$STATES where ACTIEF='1' order by CODE", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        $results = $this->db->get_results("select count(*) from FILES\$FILES WHERE STATE_ID = '$id'");

        if ($results[0]->COUNT > 0) {
            return false;
        } else {
            return true;
        }
    }
    public function getByCode($code)
    {
        return $this->db->get_row("select STATE_ID, DESCRIPTION,CODE from FILES\$STATES where CODE = '{$code}'", ARRAY_A);
    }

    public function getCurrentOrderCycle($stateId)
    {
        $cycle = $this->db->get_var("SELECT ORDER_CYCLE FROM FILES\$ACTIONS WHERE STATE_ID = {$stateId}");

        if (empty($cycle)) {
            $cycle = 0;
        }

        return $cycle;
    }


}

?>
