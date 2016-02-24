<?php

require_once 'application/models/Base.php';

class Application_Model_Debtors extends Application_Model_Base {

    public function create($data) {
        $zipCodesModel = new Application_Model_ZipCodes();

        $zipCodeId = $zipCodesModel->CheckOrCreate($data);

        $createData = array(
            'NAME' => $data['NAME'],
            'ADDRESS' => $data['ADDRESS'],
            'ZIP_CODE_ID' => $zipCodeId,
            'LANGUAGE_ID' => $data['LANGUAGE_ID'],
            'E_MAIL' => $data['E_MAIL'],
            'TELEPHONE' => $data['TELEPHONE'],
            'TELEFAX' => $data['TELEFAX'],
            'VATNR' => $data['VATNR'],
            'TRAIN_TYPE' => $data['TRAIN_TYPE'],
            'CREATION_DATE' => date("Y-m-d"),
            'CREATION_USER' => $this->online_user,
            'PASS' => '',
        );



        if (!empty($data['BIRTH_DAY'])) {
            $createData['BIRTH_DAY'] = $data['BIRTH_DAY'];
        }

        $debtorId = $this->addData('FILES$DEBTORS',$createData,'DEBTOR_ID');
        return $debtorId;
    }

    public function getAllDebtors() {
        $sql = "SELECT * FROM FILES\$DEBTORS
                WHERE DEBTOR_ID IN
                    (SELECT DEBTOR_ID FROM FILES\$FILES
                     WHERE DATE_CLOSED IS NULL OR DATE_CLOSED > (CURRENT_DATE - 7))";
        return $this->db->get_results($sql);
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
                EXTRA_FIELD = '{$data['EXTRA_FIELD']}',
                TRAIN_TYPE = '{$data['TRAIN_TYPE']}',
                CREDIT_LIMIT = '{$data['CREDIT_LIMIT']}',
                {$birthDay}
                WHERE DEBTOR_ID = {$data['DEBTOR_ID']}";

        $this->db->query($sql);

        if($data["SUPER_DEBTOR_ID"]) {
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

    public function changeDebtorScore($debtorScore, $debtorId, $userId) {

        $escUserId = $this->db->escape($userId);
        $escDebtorId = $this->db->escape($debtorId);
        $escDebtorScore = $this->db->escape($debtorScore);

        $sql = "INSERT INTO DEBTOR_SCORE
                (DEBTOR_SCORE_ID, DEBTOR_ID, USER_ID, SCORE, TIME_STAMP)
                VALUES(COALESCE((SELECT MAX(DEBTOR_SCORE_ID) + 1 FROM DEBTOR_SCORE), 1), {$escDebtorId}, {$escUserId}, {$escDebtorScore}, CURRENT_TIME)";
        $this->db->query($sql);
    }

    public function save($data, $id)
    {
        if (array_key_exists('BIRTH_DAY', $data) && empty($data['BIRTH_DAY'])) {
            unset($data['BIRTH_DAY']);
        }

        return $this->saveData('FILES\$DEBTORS', $data, 'DEBTOR_ID = ' . $id);
    }

    function getSubdebtorsByDebtorId($debtorId, $ignoredIds = array()) {
        $escDebtorId = $this->db->escape($debtorId);
        $ignoredIdsClause = '';
        if(count($ignoredIds)) {
            $escIgnoredIds = array();
            foreach($ignoredIds as $ignoredId) {
                $escIgnoredIds []= $this->db->escape($ignoredId);
            }
            $strEscIgnoredIds = implode(',', $escIgnoredIds);
            $ignoredIdsClause = " AND D.DEBTOR_ID NOT IN ({$strEscIgnoredIds})";
        }


        $sql = "SELECT D.*,F.* FROM FILES\$DEBTORS D
                JOIN FILES\$FILES F ON F.DEBTOR_ID = D.DEBTOR_ID
                WHERE D.DEBTOR_ID IN
                    (SELECT SUB_DEBTOR_ID FROM SUBDEBTORS
                    WHERE SUPER_DEBTOR_ID = {$escDebtorId})
                {$ignoredIdsClause}";
        return $this->db->get_results($sql);
    }

    function getSuperdebtorByDebtorId($debtorId) {
        $escDebtorId = $this->db->escape($debtorId);

        $sql = "SELECT * FROM FILES\$DEBTORS
                WHERE DEBTOR_ID IN
                    (SELECT SUPER_DEBTOR_ID FROM SUBDEBTORS WHERE SUB_DEBTOR_ID = {$escDebtorId})";
        return $this->db->get_row($sql);

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
              WHERE DEBTOR_ID IN (SELECT FIRST 1 SUPER_DEBTOR_ID FROM SUBDEBTORS WHERE SUB_DEBTOR_ID = D.DEBTOR_ID)) AS SUPER_DEBTOR_NAME,
              (SELECT FIRST 1 SCORE FROM DEBTOR_SCORE ds WHERE ds.DEBTOR_ID = D.DEBTOR_ID ORDER BY TIME_STAMP DESC) AS DEBTOR_SCORE
            FROM FILES\$DEBTORS_ALL_INFO D
            JOIN FILES\$DEBTORS D2 ON D2.DEBTOR_ID = D.DEBTOR_ID WHERE D.DEBTOR_ID = {$debtorId}";
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

    function getMostRecentPaymentDelayAndPaymentNrHistory($debtorId) {
        $escDebtorId = $this->db->escape($debtorId);
        $sql = "SELECT FIRST 1 * FROM PAYMENT_DELAY_AVERAGE
                WHERE DEBTOR_ID = {$escDebtorId}
                ORDER BY DATE_STAMP DESC";
        return $this->db->get_row($sql);
    }

    function getReferencesOverPaymentDelay($debtorId, $paymentDelay) {
        $escDebtorId = $this->db->escape($debtorId);
        $escPaymentDelay = $this->db->escape($paymentDelay);
        $sql = "SELECT *
                FROM FILES\$REFERENCES R
                    JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID
                WHERE F.DEBTOR_ID = {$escDebtorId}
                  AND (CURRENT_DATE - R.INVOICE_DATE) > {$escPaymentDelay}
                  AND R.SALDO_AMOUNT > 0 AND R.AMOUNT > 0";
        return $this->db->get_results($sql);
    }

    function calculatePaymentDelayAndPaymentNrInvoices($debtorId) {
        $escDebtorId = $this->db->escape($debtorId);
        $sql = "SELECT
                  AVG((SELECT FIRST 1 PAYMENT_DATE FROM FILES\$PAYMENTS WHERE REFERENCE_ID = R.REFERENCE_ID ORDER BY PAYMENT_DATE DESC)- R.INVOICE_DATE) AS PAYMENT_DELAY,
                  COUNT(*) AS NR_OF_PAYMENTS
                FROM FILES\$REFERENCES R
                    JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID
                WHERE F.DEBTOR_ID = {$escDebtorId} AND R.SALDO_AMOUNT <= 0 AND R.AMOUNT > 0";

        return $this->db->get_row($sql);
    }

    function getPaymentDelay($debtorId) {
        $sql = "SELECT AVG(COALESCE((SELECT FIRST 1 PAYMENT_DATE FROM FILES\$PAYMENTS WHERE REFERENCE_ID = R.REFERENCE_ID ORDER BY PAYMENT_DATE DESC),CURRENT_DATE)- R.INVOICE_DATE) AS DELAY_PAYMENT
                FROM FILES\$REFERENCES R
                    JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE F.DEBTOR_ID = {$debtorId}";

        $row = $this->db->get_row($sql);

        if ($row->DELAY_PAYMENT) {
            $delay = $row->DELAY_PAYMENT;
        }

        if (empty($delay)) {
            $delay = "-";
        }

        return $delay;
    }

    function getMeanPaymentDelay($collectorId = false, $compareField = 'START_DATE') {

        $extraQuery = "WHERE R.SALDO_AMOUNT <=0.00 AND R.AMOUNT > 0.00";
        if (!empty($collectorId)) {
            $extraQuery .= " AND F.COLLECTOR_ID = {$collectorId}";
        }


        $total = $this->db->get_var("SELECT SUM(AMOUNT) FROM FILES\$REFERENCES WHERE SALDO_AMOUNT <=0.00 AND AMOUNT > 0.00 ");


        $sql = "SELECT SUM((P.PAYMENT_DATE - R.{$compareField})*R.AMOUNT) AS DELAY_PAYMENT
                FROM FILES\$REFERENCES R
                    JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID
                    JOIN FILES\$PAYMENTS P ON P.REFERENCE_ID = R.REFERENCE_ID
                    $extraQuery";

        $value = $this->db->get_var($sql);

        $return = $value / $total ;

        return $return;
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

    public function getDebtorByReferenceId($referenceId) {
        $escReferenceId = $this->db->escape($referenceId);
        $sql = "SELECT * FROM FILES\$DEBTORS WHERE DEBTOR_ID IN
                    (SELECT DEBTOR_ID FROM FILES\$FILES
                     WHERE FILE_ID IN
                        (SELECT FILE_ID FROM FILES\$REFERENCES
                         WHERE REFERENCE_ID = {$escReferenceId}))";
        return $this->db->get_row($sql);
    }

    public function getDebtor($debtorId) {
        $escDebtorId = $this->db->escape($debtorId);
        $sql = "SELECT D.*,
                    (SELECT FIRST 1 CODE FROM SUPPORT\$LANGUAGES
                    WHERE LANGUAGE_ID = D.LANGUAGE_ID) AS LANGUAGE_CODE
                FROM FILES\$DEBTORS D
                WHERE D.DEBTOR_ID = {$escDebtorId}";
        return $this->db->get_row($sql);
    }

    /**
     * @deprecated getDebtorData returns an array of debtor results, even though there should only be 1 result per
     *             id. It's probably better to use getDebtor instead, which just returns the debtor object.
     * @param $debtorId
     * @return null
     */
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

    public function getDebtorByFileActionId($fileActionId) {
         $escFileActionId = $this->db->escape($fileActionId);
         $sql = "SELECT * FROM FILES\$DEBTORS WHERE DEBTOR_ID =
                    (SELECT DEBTOR_ID FROM FILES\$FILES WHERE FILE_ID =
                        (SELECT FILE_ID FROM FILES\$FILE_ACTIONS WHERE FILE_ACTION_ID = {$escFileActionId}))";
        return $this->db->get_row($sql);
    }
}

?>
