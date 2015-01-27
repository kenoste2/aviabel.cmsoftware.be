<?php

require_once 'application/models/Base.php';

class Application_Model_File extends Application_Model_Base {

    public function getClientConditions($fileId) {
        $clientId = $this->db->get_var("SELECT CLIENT_ID FROM FILES\$FILES WHERE FILE_ID = {$fileId}");
        $clientConditions = $this->db->get_row("select 
            CURRENT_INTREST_PERCENT AS INTEREST_PERCENT,CURRENT_INTREST_MINIMUM  AS INTEREST_MINIMUM
            ,CURRENT_COST_PERCENT AS COST_PERCENT, CURRENT_COST_MINIMUM AS COST_MINIMUM
            from CLIENTS\$CLIENTS where CLIENT_ID={$clientId}", ARRAY_A);
        return $clientConditions;
    }

    public function getClientId($fileId) {
        $sql = 'SELECT CLIENT_ID FROM FILES$FILES WHERE FILE_ID = ' . $fileId;
        $clientId = $this->db->get_var($sql);
        return $clientId;
    }

    public function getActionCost($fileId, $costId) {

        $clientId = $this->getClientId($fileId);
        $fileData = $this->getFileData($fileId);

        $code = $this->db->get_var("SELECT CODE FROM FILES\$COSTS WHERE COST_ID = $costId");


        $sql = "SELECT INVOICE_MINIMUM,INVOICE_PERCENT,PERCENT_MINIMUM FROM CLIENTS\$CONDITIONS
            WHERE CLIENT_ID = '$clientId'
            AND CONDITION_TYPE = '$code'  ";
        $conditions = $this->db->get_row($sql);
        if (!empty($conditions)) {
            if ($conditions->INVOICE_PERCENT > 0) {
                $cost = $fileData->AMOUNT * ($conditions->INVOICE_PERCENT / 100);
                if ($conditions->PERCENT_MINIMUM > 0) {
                    if ($cost < $conditions->PERCENT_MINIMUM) {
                        $cost = $conditions->PERCENT_MINIMUM;
                    }
                }
            }
            if ($conditions->INVOICE_MINIMUM > 0) {
                $cost = $conditions->INVOICE_MINIMUM;
            }
        } else {
            $sql = "SELECT AMOUNT FROM FILES\$COSTS WHERE COST_ID = $costId";
            $cost = $this->db->get_var($sql);
        }
        return $cost;
    }

    function getFileData($fileId) {
        $sql = "SELECT * FROM FILES\$FILES WHERE FILE_ID = $fileId";
        $row = $this->db->get_row($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    function getFileViewData($fileId) {
        $sql = "SELECT * FROM FILES\$FILES_ALL_INFO WHERE FILE_ID = $fileId";
        $row = $this->db->get_row($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    function getFileAmounts ($fileId, $dispute = false) {


        if ($dispute  == true ) {
            $sql = "SELECT SUM(AMOUNT) AS AMOUNT, SUM(INTEREST) AS INTEREST, SUM(COSTS) AS COSTS , SUM(AMOUNT+COSTS+INTEREST) AS TOTAL,
              SUM(AMOUNT+COSTS+INTEREST) AS PAYABLE, SUM(PAYED_AMOUNT) AS PAYED_AMOUNT,SUM(PAYED_INTEREST) AS PAYED_INTEREST,SUM(PAYED_COSTS) AS PAYED_COSTS,
              SUM(PAYED_AMOUNT+PAYED_INTEREST+PAYED_COSTS) AS PAYED_TOTAL , SUM(SALDO_AMOUNT+SALDO_COSTS+SALDO_INTEREST) AS SALDO
              FROM FILES\$REFERENCES WHERE FILE_ID = {$fileId} AND DISPUTE = 0";
        } else {
            $sql = "SELECT A.AMOUNT,A.INTEREST,A.COSTS,(A.TOTAL+A.INCASSOKOST) AS TOTAL,
              (A.PAYABLE+A.INCASSOKOST) AS PAYABLE,A.PAYED_AMOUNT,A.PAYED_INTEREST,A.PAYED_COSTS
              ,A.PAYED_UNKNOWN,A.PAYED_TOTAL,(A.SALDO+A.INCASSOKOST) AS SALDO
              FROM FILES\$FILES_ALL_INFO A WHERE A.FILE_ID = {$fileId}";

        }

        $row = $this->db->get_row($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    function getFileField($fileId,$field) {
        $sql = "SELECT {$field} FROM FILES\$FILES_ALL_INFO WHERE FILE_ID = $fileId";
        $row = $this->db->get_var($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }


    public function getNextAction($id) {
        $file = $this->db->get_row("SELECT STATE_CODE FROM FILES\$FILES_ALL_INFO WHERE FILE_ID={$id}");
        $lastActionDate = $this->db->get_var("SELECT ACTION_DATE FROM FILES\$FILE_ACTIONS_ALL_INFO WHERE FILE_ID= {$id} AND ACTION_CODE = '{$file->STATE_CODE}' ORDER BY FILE_ACTION_ID DESC");

        $sql = "SELECT ACTION_ID, ACTION_DESCRIPTION, ACTION_DATE, ACTION_CODE FROM FILES\$FILE_ACTIONS_ALL_INFO WHERE FILE_ID = {$id} AND ACTION_DATE > CURRENT_DATE";
        if ($row = $this->db->get_row($sql)) {
            $result['date'] = $row->ACTION_DATE;
            $result['actionCode'] = $row->ACTION_CODE;
            $result['actionDescription'] = $row->ACTION_DESCRIPTION;
            $result['actionId'] = $row->ACTION_ID;
            return $result;
        } else {
            $result = array();
            $sql = "select * from TRAIN where VISIBLE='Y' ORDER BY DAYS";
            if ($results_train = $this->db->get_results($sql)) {
                foreach ($results_train as $train_modules) {
                    $sql = "select DISTINCT I.FILE_ID ".str_replace("`","'",$train_modules->SQL)." AND I.FILE_ID = {$id}";
                    $sql = str_replace("and ACTION_DATE>=CURRENT_DATE-{$train_modules->DAYS}", "",$sql);
                    $sql = str_replace("AND F.ACTION_DATE=CURRENT_DATE-{$train_modules->DAYS}", "",$sql);
                    $sql = str_replace("and F.ACTION_DATE=CURRENT_DATE-{$train_modules->DAYS}", "",$sql);
                    $sql = str_replace("and CREATION_DATE>CURRENT_DATE-{$train_modules->DAYS}", "",$sql);
                    $sql = str_replace("I.LAST_ACTION_DATE=CURRENT_DATE-{$train_modules->DAYS}", "1=1",$sql);
                    $sql .= " AND CLIENT_ID IN (SELECT CLIENT_ID FROM CLIENTS\$CLIENTS WHERE TRAIN_TYPE = '{$train_modules->TRAIN_TYPE}')";

                    if ($this->db->get_var($sql)  AND empty($result)) {
                        $nextDate = date("Y-m-d", strtotime($lastActionDate . " 00:00:00") + (86400 * $train_modules->DAYS));
                        $nextAction = $this->db->get_row("SELECT CODE,DESCRIPTION FROM FILES\$ACTIONS WHERE ACTION_ID={$train_modules->SETACTION}");
                        $result['date'] = $nextDate;
                        $result['actionCode'] = $nextAction->CODE;
                        $result['actionDescription'] = $nextAction->DESCRIPTION;
                        $result['actionId'] = $nextAction->SETACTION;
                        $result['trainDescription'] = $train_modules->DESCRIPTION;
                    }
                }
            }
        }
        return $result;
    }



}

?>
