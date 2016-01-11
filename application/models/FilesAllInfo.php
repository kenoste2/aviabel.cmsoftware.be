<?php

require_once 'application/models/Base.php';

class Application_Model_FilesAllInfo extends Application_Model_Base
{
    public function getAllInfo()
    {
        return $this->db->get_row("select FIRST 1  * from FILES\$FILES_ALL_INFO", ARRAY_A);
    }

    public function getRealtimeSummary($collectorId = false)
    {
        $client_query = $this->getClientQuery();

        $extraQuery = "";
        if (!empty($collectorId)) {
            $extraQuery .= " AND COLLECTOR_ID = {$collectorId}";
        }


        return $this->db->get_results("select STATE_CODE,count(DISTINCT FILE_ID) as COUNTER
          ,sum(AMOUNT) as AMOUNT
          ,sum(INTEREST) as INTEREST
          ,sum(COSTS) as COSTS
          ,sum(TOTAL) as TOTAL
          ,sum(PAYED_AMOUNT) as PAYED_AMOUNT
          ,sum(PAYED_INTEREST) as PAYED_INTEREST
          ,sum(PAYED_COSTS) as PAYED_COSTS
          ,sum(PAYED_AMOUNT+PAYED_INTEREST+PAYED_COSTS) as PAYED_TOTAL
          from files\$files_all_info  WHERE DATE_CLOSED IS null AND STATE_CODE NOT IN ('NEW') $client_query $extraQuery group by state_code order by PAYED_AMOUNT DESC");
    }

    public function getRealtimeSummaryTotal($collectorId = false)
    {
        $client_query = $this->getClientQuery();

        $extraQuery = "";
        if (!empty($collectorId)) {
            $extraQuery .= " AND COLLECTOR_ID = {$collectorId}";
        }


        return $this->db->get_row("select count(*) as COUNTER
            ,sum(AMOUNT) as AMOUNT
            ,sum(INTEREST) as INTEREST
            ,sum(COSTS) as COSTS
            ,sum(TOTAL) as TOTAL
            ,sum(PAYED_AMOUNT) as PAYED_AMOUNT
            ,sum(PAYED_INTEREST) as PAYED_INTEREST
            ,sum(PAYED_COSTS) as PAYED_COSTS
            ,sum(PAYED_AMOUNT+PAYED_INTEREST+PAYED_COSTS) as PAYED_TOTAL
            from files\$files_all_info WHERE DATE_CLOSED IS null AND STATE_CODE NOT IN ('NEW') $client_query $extraQuery ");
    }

    protected function getClientQuery()
    {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');

        $client_query = '';
        if ($authNamespace->online_rights == 5) {
            $client_query = 'AND CLIENT_ID='.$authNamespace->online_client_id;
        }

        return $client_query;
    }
}

?>
