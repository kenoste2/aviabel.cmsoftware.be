<?php

require_once 'application/models/Base.php';

class Application_Model_Debtors extends Application_Model_Base {

    public function create($data) {
        $zipCodesModel = new Application_Model_ZipCodes();

        $zipCodeId = $zipCodesModel->CheckOrCreate($data);

        $birthDay = (!empty($data['BIRTH_DAY'])) ? "'{$data['BIRTH_DAY']}'" : 'null';


        $sql = "insert into FILES\$DEBTORS 
            (NAME,ADDRESS,ZIP_CODE_ID,LANGUAGE_ID,E_MAIL,TELEPHONE,TELEFAX,VATNR,CREATION_DATE,CREATION_USER,TRAIN_TYPE,PASS)
            values ('{$data['NAME']}','{$data['ADDRESS']}',{$zipCodeId},'{$data['LANGUAGE_ID']}'
                ,'{$data['E_MAIL']}','{$data['TELEPHONE']}','{$data['TELEFAX']}'
                ,'{$data['VATNR']}',CURRENT_DATE,'{$this->online_user}','{$data['TRAIN_TYPE']}','')
            RETURNING DEBTOR_ID";
        $debtorId = $this->db->get_var($sql);
        return $debtorId;
    }

    public function add($data)
    {
        if (!array_key_exists('ZIP_CODE_ID', $data)) {
            $zipCodesModel = new Application_Model_ZipCodes();
            $data['ZIP_CODE_ID'] = $zipCodesModel->CheckOrCreate($data);
        }

        $data['CREATION_DATE'] = date('Y-m-d');
        $data['CREATION_USER'] = $this->online_user;

        if (array_key_exists('BIRTH_DAY', $data) && empty($data['BIRTH_DAY'])) {
            unset($data['BIRTH_DAY']);
        }

        return $this->addData("FILES\$DEBTORS", $data, 'DEBTOR_ID');
    }

    public function update($data) {
        $zipCodesModel = new Application_Model_ZipCodes();
        $zipCodeId = $zipCodesModel->CheckOrCreate($data);
        $birthDay = (!empty($data['BIRTH_DAY'])) ? "BIRTH_DAY = '{$data['BIRTH_DAY']}'" : 'BIRTH_DAY = null';

        $sql = "UPDATE FILES\$DEBTORS
            SET NAME = '{$data['NAME']}',
                ADDRESS = '{$data['ADDRESS']}',
                ZIP_CODE_ID = $zipCodeId,
                LANGUAGE_ID = '{$data['LANGUAGE_ID']}',
                E_MAIL = '{$data['E_MAIL']}',
                TELEPHONE = '{$data['TELEPHONE']}',
                TELEFAX =  '{$data['TELEFAX']}',
                GSM =  '{$data['GSM']}',    
                VATNR= '{$data['VATNR']}',
                TRAIN_TYPE = '{$data['TRAIN_TYPE']}',
                CREDIT_LIMIT = '{$data['CREDIT_LIMIT']}',
                $birthDay
                WHERE DEBTOR_ID = {$data['DEBTOR_ID']}";

        $this->db->query($sql);

        if(isset($data["SUPER_DEBTOR_ID"])) {
            $this->addSubDebtor($data["SUPER_DEBTOR_ID"], $data['DEBTOR_ID']);
        }
    }

    private function addSubDebtor($superDebtorId, $subDebtorId) {
        $escSuperDebtorId = $this->db->escape($superDebtorId);
        $escSubDebtorId = $this->db->escape($subDebtorId);

        $sqlDelete = "DELETE FROM SUBDEBTORS WHERE SUB_DEBTOR_ID = {$escSubDebtorId}";
        $this->db->query($sqlDelete);

        $sql = "INSERT INTO SUBDEBTORS (SUPER_DEBTOR_ID, SUB_DEBTOR_ID) VALUES ({$escSuperDebtorId}, {$escSubDebtorId})";
        $this->db->query($sql);
    }

    public function save($data, $id)
    {
        if (array_key_exists('BIRTH_DAY', $data) && empty($data['BIRTH_DAY'])) {
            unset($data['BIRTH_DAY']);
        }

        return $this->saveData('FILES\$DEBTORS', $data, 'DEBTOR_ID = ' . $id);
    }

    function getDebtorsQuery($data) {
        $query_debtors = "";

        if ($this->auth->online_rights == 5) {
            if (!empty($this->auth->online_subclients)) {
                $query_debtors.=" and B.CLIENT_ID = '{$this->auth->online_subclients}' ";
            } else {
                $query_debtors .= " AND (B.CLIENT_ID = {$this->auth->online_client_id} ";
                foreach ($this->auth->online_subclients as $value) {
                    $query_debtors .=" OR B.CLIENT_ID = $value";
                }
                $query_debtors .= ")";
            }
        }

        if (!empty($data['name'])) {
            $query_debtors.=" and A.NAME CONTAINING '{$data['name']}'";
        }

        if (!empty($data['vat_no'])) {
            $query_debtors.= " and A.VATNR CONTAINING '{$data['vat_no']}'";
        }
        if (!empty($data['address'])) {
            $query_debtors.=" and A.ADDRESS CONTAINING '{$data['address']}'";
        }
        if (!empty($data['zip_code'])) {
            $query_debtors.=" and A.ZIP_CODE = '{$data['zip_code']}'";
        }
        if (!empty($data['city'])) {
            $query_debtors.=" and A.CITY CONTAINING '{$data['city']}'";
        }
        return $query_debtors;
    }

    function getArrayData($debtorId) {
        $sql = "SELECT D.*, D2.TRAIN_TYPE, D2.CREDIT_LIMIT,
            (SELECT FIRST 1 SUPER_DEBTOR_ID FROM SUBDEBTORS WHERE SUB_DEBTOR_ID = D.DEBTOR_ID) AS SUPER_DEBTOR_ID,
            (SELECT FIRST 1 NAME FROM FILES\$DEBTORS
              WHERE DEBTOR_ID IN (SELECT FIRST 1 SUPER_DEBTOR_ID FROM SUBDEBTORS WHERE SUB_DEBTOR_ID = D.DEBTOR_ID)) AS SUPER_DEBTOR_NAME
            FROM FILES\$DEBTORS_ALL_INFO D
            JOIN FILES\$DEBTORS D2 ON D2.DEBTOR_ID = D.DEBTOR_ID WHERE D.DEBTOR_ID = $debtorId";
        $row = $this->db->get_row($sql, ARRAY_A);

        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    function getDebtorByNameAndBirthday($name, $birthday)
    {
        return $this->db->get_row("select DEBTOR_ID from FILES\$DEBTORS_ALL_INFO where NAME='" . $this->db->escape($name) . "' AND BIRTH_DAY='$birthday'");
    }

    function getDebtorByNameAndAddress($name, $address)
    {
        return $this->db->get_row("select DEBTOR_ID from FILES\$DEBTORS_ALL_INFO where NAME='" . $this->db->escape($name) . "' AND ADDRESS='".$this->db->escape($address)."'");
    }

    function getOpenAmount($debtorId) {
        return $this->db->get_var("select SUM(PAYABLE+INCASSOKOST) from FILES\$FILES where DEBTOR_ID={$debtorId} and DATE_CLOSED is null");
    }

    function getTotalAmount($debtorId) {
        return $this->db->get_var("select SUM(PAYABLE+INCASSOKOST) from FILES\$FILES where DEBTOR_ID={$debtorId}");
    }

    function getTrajectType($debtorId)
    {
        return $this->db->get_var("SELECT TRAIN_TYPE FROM FILES\$DEBTORS WHERE DEBTOR_ID = {$debtorId}");
    }

    function getPaymentDelay($debtorId) {
        $sql = "SELECT AVG(COALESCE((SELECT PAYMENT_DATE FROM FILES\$PAYMENTS WHERE REFERENCE_ID = R.REFERENCE_ID),CURRENT_DATE)- R.INVOICE_DATE) AS DELAY_PAYMENT
                FROM FILES\$REFERENCES R
                    JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE F.DEBTOR_ID = {$debtorId}";

        $row = $this->db->get_row($sql);

        if ($row->DELAY_PAYMENT) {
            $delay = $row->DELAY_PAYMENT;
        } else {
            $delay = $row->DELAY_NOPAYMENT;
        }
        if (empty($delay)) {
            $delay = "-";
        }

        return $delay;
    }
    
    public function getAllFiles($debtorId) {
        $results = $this->db->get_results("SELECT FILE_NR,CREATION_DATE,STATE_CODE,REFERENCE,LAST_ACTION_DATE,FILE_ID,
            (TOTAL+INCASSOKOST) AS TOTAL,(PAYABLE+INCASSOKOST-PAYED_UNKNOWN) AS PAYABLE FROM FILES\$FILES_ALL_INFO WHERE DEBTOR_ID = {$debtorId}");
        return $results;
    }
    public function getHistory($debtorId) {
        $results = $this->db->get_results("select A.CREATION_DATE,A.CREATION_USER,A.NAME,A.ADDRESS,A.E_MAIL,A.TELEPHONE,A.TELEFAX,A.ZIP_CODE_ID,B.CODE,B.CITY from 
            FILES\$DEBTORS_HISTORY A
            JOIN SUPPORT\$ZIP_CODES B ON A.ZIP_CODE_ID = B.ZIP_CODE_ID
            where DEBTOR_ID='{$debtorId}' order by RECORD_ID DESC");
        return $results;
    }


    public function getDebtorData($debtorId) {
        $results = $this->db->get_results("SELECT * FROM FILES\$DEBTORS WHERE DEBTOR_ID = {$debtorId}");
        return $results;
    }

    public function getCurrentPaymentDelays() {
        $sql = "SELECT F.DEBTOR_ID,AVG(COALESCE((SELECT PAYMENT_DATE FROM FILES\$PAYMENTS WHERE REFERENCE_ID = R.REFERENCE_ID),CURRENT_DATE)- R.INVOICE_DATE) AS DELAY
                    FROM FILES\$REFERENCES R
                    JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID GROUP BY DEBTOR_ID";

        $results = $this->db->get_results($sql);
        return $results;
    }

    public function getDebtorField($debtorId,$field) {
        $sql = "SELECT {$field} FROM FILES\$DEBTORS_ALL_INFO WHERE DEBTOR_ID = $debtorId";
        $row = $this->db->get_var($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }




    

}

?>
