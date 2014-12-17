<?php

require_once 'application/models/Base.php';

class Application_Model_Clients extends Application_Model_Base {

    public function getConditions($clientId) {
        $row = $this->db->get_row("select CURRENT_INTREST_PERCENT,CURRENT_INTREST_MINIMUM,CURRENT_COST_PERCENT,CURRENT_COST_MINIMUM 
            from CLIENTS\$CLIENTS where CLIENT_ID=$clientId", ARRAY_A);
        $row['AUTO_CALCULATE'] = 1;
        return $row;
    }

    public function getClientsQuery($data) {
        $query = "";

        if (!empty($data['NAME'])) {
            $query.=" and NAME CONTAINING '{$data['NAME']}'";
        }

        if (!empty($data['CODE'])) {
            $query.= " and CODE CONTAINING '{$data['CODE']}'";
        }
        if (!empty($data['VAT_NO'])) {
            $query.=" and VAT_NO CONTAINING '{$data['VAT_NO']}'";
        }
        if (!empty($data['ADDRESS'])) {
            $query.=" and ADDRESS CONTAINING '{$data['ADDRESS']}'";
        }
        return $query;
    }

    public function getTotals($query) {
        $sql = "SELECT COUNT(*) AS COUNTER,SUM(FILE_COUNT) as FILE_COUNT,SUM(OPEN_FILES) AS OPEN_FILES 
            FROM CLIENTS\$CLIENTS_ALL_INFO A WHERE IS_ACTIVE=1 {$query}";
        return $this->db->get_row($sql);
    }
    public function getTemplateFooter($clientId) {
        $sql = "SELECT TEMPLATE_FOOTER
            FROM CLIENTS\$CLIENTS
            WHERE CLIENT_ID = {$clientId}";
        return $this->db->get_var($sql);
    }



    function getArrayData($clientId) {
        $sql = "SELECT  A.*, B.TRAIN_TYPE,C.COUNTRY_ID,D.COUNTRY_ID AS INVOICE_COUNTRY_ID,B.ARTICLE,B.COURT,B.ACTIVITIES, B.TEMPLATE_FOOTER FROM
            CLIENTS\$CLIENTS_ALL_INFO A
            JOIN CLIENTS\$CLIENTS B ON A.CLIENT_ID = B.CLIENT_ID
            JOIN SUPPORT\$ZIP_CODES C ON A.ZIP_CODE_ID = C.ZIP_CODE_ID
            JOIN SUPPORT\$ZIP_CODES D ON A.INVOICE_ZIP_ID = D.ZIP_CODE_ID
            WHERE A.CLIENT_ID = $clientId";
        $row = $this->db->get_row($sql, ARRAY_A);

        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    function getArrayClients() {
        $sql = "SELECT CLIENT_ID,NAME FROM CLIENTS\$CLIENTS_ALL_INFO WHERE IS_ACTIVE=1 ORDER BY NAME";
        $array = array ("-" => "");
        $results = $this->db->get_results($sql, ARRAY_N);
        $merge = array_merge($array,$results);
        return $merge;
    }

    function getAllClients($query_extra = '', $maxRecords = 99999999)
    {
        $totals = $this->getTotals($query_extra);

        $select = 'SELECT';
        if ($totals->COUNTER > $maxRecords) {
            $select = 'SELECT FIRST ' . $maxRecords;
            $onlyFirst = $maxRecords;
        }

        $sql = $select . " * FROM CLIENTS\$CLIENTS_ALL_INFO WHERE IS_ACTIVE=1 $query_extra ORDER BY NAME";
        $results = $this->db->get_results($sql);

        return array($results, $totals, $onlyFirst, $sql);
    }

    public function getClientIdByCode($code)
    {
        return $this->db->get_var("select CLIENT_ID from CLIENTS\$CLIENTS where CODE='" . trim($code) . "' AND CODE!=''");
    }

    public function getClientIdById($id)
    {
        return $this->db->get_row("select * from CLIENTS\$CLIENTS where CLIENT_ID='" . $id . "'");
    }

    public function update($data, $clientId) {
        $zipCodesModel = new Application_Model_ZipCodes();
        $zipCodeId = $zipCodesModel->CheckOrCreate($data);

        $data['ZIP_CODE_ID'] = $zipCodeId;
        unset($data['ZIP_CODE']);
        unset($data['CITY']);
        unset($data['COUNTRY_ID']);

        if (!empty($data['MAINCLIENT'])) {
            $this->_addMainClient($data['MAINCLIENT'], $clientId);
        } else {
            $this->_deleteMainClient($clientId);
        }
        unset($data['MAINCLIENT']);

        $invoiceZipArray = array(
            'COUNTRY_ID' => $data['INVOICE_COUNTRY_ID'],
            'ZIP_CODE' => $data['INVOICE_ZIP_CODE'],
            'CITY' => $data['INVOICE_CITY']
        );
        $invoiceZipCodeId = $zipCodesModel->CheckOrCreate($invoiceZipArray);
        $data['INVOICE_ZIP_ID'] = $invoiceZipCodeId;
        unset($data['INVOICE_ZIP_CODE']);
        unset($data['INVOICE_CITY']);
        unset($data['INVOICE_COUNTRY_ID']);

        $this->saveData('CLIENTS$CLIENTS', $data, "CLIENT_ID = {$clientId}");
    }

    public function add($data) {
        $zipCodesModel = new Application_Model_ZipCodes();
        $zipCodeId = $zipCodesModel->CheckOrCreate($data);

        $data['ZIP_CODE_ID'] = $zipCodeId;
        unset($data['ZIP_CODE']);
        unset($data['CITY']);
        unset($data['COUNTRY_ID']);

        $mainClient = $data['MAINCLIENT'];

        unset($data['MAINCLIENT']);

        $invoiceZipArray = array(
            'COUNTRY_ID' => $data['INVOICE_COUNTRY_ID'],
            'ZIP_CODE' => $data['INVOICE_ZIP_CODE'],
            'CITY' => $data['INVOICE_CITY']
        );
        $invoiceZipCodeId = $zipCodesModel->CheckOrCreate($invoiceZipArray);
        $data['INVOICE_ZIP_ID'] = $invoiceZipCodeId;
        unset($data['INVOICE_ZIP_CODE']);
        unset($data['INVOICE_CITY']);
        unset($data['INVOICE_COUNTRY_ID']);

        unset($data['PASSWORD']);
        unset($data['PASSWORD2']);

        $data['IS_ACTIVE'] = 1;

        $clientId = $this->addData('CLIENTS$CLIENTS', $data, 'CLIENT_ID');


        if (!empty($mainClient)) {
            $this->_addMainClient($mainClient, $clientId);
        } else {
            $this->_deleteMainClient($clientId);
        }

        $this->addGeneralFile($clientId);
        $this->addGeneralAccount($clientId);
        $this->createClientCommission($clientId);

        return $clientId;
    }


    public function addGeneralFile($clientId)
    {
        $data = array(
            'CODE' => "GENERAL",
            'DESCRIPTION' => 'General',
            'CLIENT_ID' => $clientId,
            'VISIBLE' => 'Y',
        );
        $this->addData('CLIENTS$FILE_TYPES',$data);
    }
    public function addGeneralAccount($clientId)
    {
        $data = array(
            'CLIENT_ID' => $clientId,
            'ACCOUNT_NO' => '000-0000000-97',
        );
        $this->addData('CLIENTS$CLIENT_ACCOUNTS',$data);
    }

    public function save($data, $clientId)
    {
        $this->saveData('CLIENTS$CLIENTS', $data, "CLIENT_ID = {$clientId}");
    }

    function getClientViewData($clientId) {
        $sql = "SELECT A.*,B.ARTICLE,B.COURT,B.ACTIVITIES FROM CLIENTS\$CLIENTS_ALL_INFO A
          JOIN CLIENTS\$CLIENTS B ON A.CLIENT_ID = B.CLIENT_ID
        WHERE A.CLIENT_ID = $clientId";
        $row = $this->db->get_row($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    function getClientField($clientId,$field) {
        $sql = "SELECT {$field} FROM CLIENTS\$CLIENTS_ALL_INFO WHERE CLIENT_ID = $clientId";
        $row = $this->db->get_var($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }


    public function hide($clientId)
    {
        $data = array(
            'IS_ACTIVE' => 0
        );
        $this->saveData('CLIENTS$CLIENTS', $data, "CLIENT_ID = {$clientId}");
    }


    public function getMainClient($clientId) {

        return $this->db->get_var("SELECT KLANTID FROM SUBCLIENTS WHERE ID = {$clientId}");

    }

    public function createClientCommission ($clientId) {

       $conditionsExists = $this->db->get_var("SELECT count(*) FROM CLIENTS\$CONDITIONS WHERE CLIENT_ID = {$clientId}
        AND CONDITION_TYPE = 'C'");

        if (empty($conditionsExists)) {

            $selectClientId = $this->functions->getUserSetting('COMMISSION_CLIENT_ID');
            if (empty($selectClientId)) {
                $selectClientId = $this->db->get_var("SELECT MAX(CLIENT_ID) FROM CLIENTS\$CONDITIONS WHERE CONDITION_TYPE = 'C'");
            }
            $params = $this->db->get_results("SELECT * FROM CLIENTS\$CONDITIONS WHERE
                      CLIENT_ID  = {$selectClientId} AND CONDITION_TYPE = 'C'
                      ORDER BY END_VALUE");

            foreach ($params as $param) {
                $data = array (
                    'CLIENT_ID' => $clientId,
                    'CONDITION_TYPE' => 'C',
                    'FROM_DATE' => date("Y-m-d"),
                    'END_VALUE' => $param->END_VALUE,
                    'INVOICE_PERCENT' => $param->INVOICE_PERCENT,
                    'INVOICE_MINIMUM' => $param->INVOICE_MINIMUM,
                    'PERCENT_MINIMUM' => 0,
                );
                $this->saveData('CLIENTS$CONDITIONS', $data);
            }

        }
    }


    private function _addMainClient($mainClient, $clientId) {
        $this->_deleteMainClient($clientId);
        $name = $this->db->get_var("SELECT CODE FROM CLIENTS\$CLIENTS WHERE CLIENT_ID = {$mainClient}");

        $data = array(
            'ID' => $clientId,
            'KLANTID' => $mainClient,
            'KLANTNAAM' => $name
        );
        $this->addData('SUBCLIENTS', $data);
    }

    private function _deleteMainClient($clientId) {
        $sql = "DELETE FROM SUBCLIENTS WHERE ID = {$clientId}";
        $this->db->query($sql);
    }




}

?>
