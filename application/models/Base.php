<?php

class Application_Model_Base {

    protected $db;
    protected $online_user;
    public $functions;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->functions = new Application_Model_CommonFunctions;
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        $this->online_user = $authNamespace->online_user;
    }

    public function addData($tableName, $data, $returnField = false, $escapeSql = false) {
        return $this->functions->saveData($tableName, $data, false , $returnField, $escapeSql);
    }

    public function saveData($tableName, $data, $where = false) {
        return $this->functions->saveData($tableName, $data, $where, false);
    }

    protected function dateDbFormat($date)
    {
        $functions = new Application_Model_CommonFunctions();
        if ($this->isNotInDbFormat($date)) {
            $date = $functions->date_dbformat($date);
        }
        return $date;
    }

    protected function isNotInDbFormat($date)
    {
        if (stripos($date, "-")) {
            list($day, $month, $year) = explode("-", $date);
        }
        else {
           list($day, $month, $year) = explode("/", $date);
        }

        if(strlen($day) > 2) {
            return false;
        }
        return true;
    }

    protected function clearEmptyValues ($data) {

        foreach ($data as $key => $value) {
            if ($value == '') {
                unset($data[$key]);
            }
        }

        return $data;

    }


    public function log($remark, $logtype = 'default')
    {
        $data = array (
            'LOGTYPE' => $logtype,
            'REMARK' => $remark,
            'CREATION_USER' => $this->online_user,
            'CREATION_DATE' => date("Y-m-d"),
        );
        $this->saveData('LOGGING', $data);
    }


}

?>
