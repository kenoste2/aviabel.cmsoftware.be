<?php

class Application_Model_AuthAdapterDbTable {

    protected $username = "";
    protected $password = "";
    protected $valid = false;

    public function setIdentity($userName) {
        $this->username = $userName;
    }

    public function setCredential($password) {
        $this->password = $password;
    }

    public function authenticate() {
        require_once 'library/ez_sql.php';
        $config = Zend_Registry::get('config');
        $this->db = new db($config->db->user, $config->db->password, $config->db->dbfile, $config->db->charset);

        $sql = "select * from SYSTEM\$USERS where CODE='{$this->username}'";
        $row = $this->db->get_row($sql);
        $userId = $row->USER_ID;
        
        if (!empty($row)) {
            if ($row->PASS == $this->password) {
                $authNamespace = new Zend_Session_Namespace('Zend_Auth');


                $authNamespace->online_user_id = $userId;
                $authNamespace->online_rights = $this->db->get_var("select RIGHTS from SYSTEM\$USERS where CODE='{$this->username}'");

                if ($authNamespace->online_rights == 5) {
                    $authNamespace->online_client_id = $this->db->get_var("select CLIENT_ID from SYSTEM\$USERS where CODE='{$this->username}'");
                    $results = $this->db->get_results("SELECT ID FROM SUBCLIENTS WHERE KLANTID = '$authNamespace->online_client_id'");
                    if (!empty($results)) {
                        $subclients = array();
                        foreach ($results as $row) {
                            $subclients[] = $row->ID;
                        }
                        $authNamespace->online_subclients = $subclients;
                    } else {
                        $authNamespace->online_subclients = array();
                    }
                }
                if ($authNamespace->online_rights == 6) {
                    $authNamespace->online_collector_id = $this->db->get_var("select COLLECTOR_ID from SYSTEM\$USERS where CODE='{$this->username}'");
                }

                $authNamespace->online_type = "INTERNAL";
                $authNamespace->online_user = $this->username;
                $authNamespace->online_name = $row->NAME;
                
                
                $menu = new Application_Model_Menu();
                $authNamespace->menu = $menu->getIniMenu($authNamespace->online_rights);
                
                $this->valid = true;
            }
            else
                return "no correct user/password";
        }
        else
            return "no correct user/password";
    }

    
    public function isValid() {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        
        if (!empty($authNamespace->online_user)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function hasIdentity() {
        return $this->isValid();
    }
    
    public function clearIdentity() {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        unset($authNamespace->online_user);
    }
    
    public function getIdentityName() {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        return $authNamespace->online_name;
    }
    public function getIdentity() {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        return $authNamespace->online_user;
    }
    
    
}

