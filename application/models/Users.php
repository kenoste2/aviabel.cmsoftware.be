<?php

require_once 'application/models/Base.php';

class Application_Model_Users extends Application_Model_Base {

    public function updatePassword($userId,$pasword) {
        $data = array(
          "PASS" => sha1($pasword),  
        );
        $this->saveData('SYSTEM$USERS', $data, "USER_ID = {$userId}");
    }
    
    
    public function createUser($data) {
        $data['PASS'] = sha1($data['PASS']);

        $data = $this->setDefaults($data);

        $userRights = array();
        if (array_key_exists('USER_RIGHTS', $data)) {
            $userRights = is_null($data['USER_RIGHTS']) ? array() : $data['USER_RIGHTS'];
        }
        unset($data['USER_RIGHTS']);
        $id = $this->addData('SYSTEM$USERS', $data, 'USER_ID');

        if (is_array($userRights)) {
            $userRightsModel = new Application_Model_UserRights();
            $userRightsModel->saveUserRights($userRights, $id);
        }
    }
    
    public function getUserByClientId($clientId) {
        $userId = $this->db->get_var("SELECT USER_ID FROM SYSTEM\$USERS WHERE CLIENT_ID = '$clientId'");
        return $userId;
    }

    public function getUsers()
    {
        return $this->db->get_results("SELECT USER_ID FROM SYSTEM\$USERS");
    }

    public function getUser($user_id)
    {
        return $this->db->get_row("SELECT * FROM SYSTEM\$USERS WHERE USER_ID = " . $user_id);
    }

    function getLoggedInUser() {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        return $this->getUser($authNamespace->online_user_id);
    }

    public function getAllUsers()
    {
        return $this->db->get_results("SELECT * FROM SYSTEM\$USERS");
    }

    public function save($data, $id)
    {
        if (empty($data['PASS'])) {
            unset($data['PASS']);
        } else {
            $data['PASS'] = sha1($data['PASS']);
        }



        $userRights = array();
        if (isset($data['USER_RIGHTS'])) {
            $userRights = $data['USER_RIGHTS'];
            unset($data['USER_RIGHTS']);
        }

        if (empty($data['CLIENT_ID'])) {
            unset($data['CLIENT_ID']);
        }
        if (empty($data['COLLECTOR_ID'])) {
            unset($data['COLLECTOR_ID']);
        }

        print "<pre>";
        print_r($data);

        unset($data['USER_RIGHTS']);
        $this->saveData("SYSTEM\$USERS", $data, 'USER_ID = ' . $id);

        if (isset($userRights)) {
            $userRightsModel = new Application_Model_UserRights();
            $userRightsModel->saveUserRights($userRights, $id);
        }
    }

    public function delete($id)
    {
        $this->db->query("DELETE FROM  SYSTEM\$USERS where USER_ID='$id'");
        $userRightsModel = new Application_Model_UserRights();
        $userRightsModel->deleteByUserId($id);
    }

    public function checkIsDeletable($user_id)
    {
        return true;
    }

    protected function setDefaults(array $data)
    {
        if (array_key_exists('CLIENT_ID', $data) && empty($data['CLIENT_ID'])) {
            $data['CLIENT_ID'] = 0;
        }

        if (array_key_exists('COLLECTOR_ID', $data) && empty($data['COLLECTOR_ID'])) {
            $data['COLLECTOR_ID'] = 0;
        }

        if (empty($data['ZIP_CODE_ID'])) {
            global $config;
            $data['ZIP_CODE_ID'] = $config->defaultPostalCode;
        }

        return $data;
    }

    public function getByCode($code)
    {
        return $this->db->get_row("select USER_ID,CODE from SYSTEM\$USERS where CODE = '{$code}'", ARRAY_A);
    }

}

?>
