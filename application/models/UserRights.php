<?php

require_once 'application/models/Base.php';

class Application_Model_UserRights extends Application_Model_Base {

    public function getUserRights($user_id)
    {
        return $this->db->get_row("select * from SYSTEM\$USER_RIGHTS where USER_ID='{$user_id}'", ARRAY_A);
    }

    public function getUserRightByColumn($userId, $column)
    {
        return $this->db->get_var("select count(*) from SYSTEM\$USER_RIGHTS where USER_ID = $userId and $column = 'Y'");
    }

    public function saveUserRights($data, $user_id)
    {
        $count = $this->db->get_var("select count(*) from SYSTEM\$USER_RIGHTS where USER_ID='{$user_id}'");

        if($count > 0){
            $row = $this->getUserRights($user_id);

            foreach (array_keys($row) as $key) {
                if (!in_array($key, array('RIGHT_ID','USER_ID'))) {
                    if (in_array($key, $data)) {
                        $row[$key] = 'Y';
                    } else {
                        $row[$key] = 'N';
                    }
                }
            }

            $this->saveData("SYSTEM\$USER_RIGHTS", $row, 'USER_ID = ' . $user_id);
        } else {
            $add = array('USER_ID' => $user_id);
            foreach ($data as $key) {
                $add[$key] = 'Y';
            }

            try{
                $this->addData("SYSTEM\$USER_RIGHTS", $add);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function deleteByUserId($user_id)
    {
        return $this->db->get_results("delete from SYSTEM\$USER_RIGHTS where USER_ID='{$user_id}'");
    }

    public function getYesFields(array $data)
    {
        $keys = array();
        foreach ($data as $key => $val) {
            if ($val == 'Y') {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    public function hasRights($userId, $resource) {
        if ($this->getUserRightByColumn($userId, $resource)) {
            return true;
        } else {
            return false;
        }
    }


}

?>
