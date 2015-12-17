<?php

require_once 'application/models/Base.php';

class Application_Model_PaymentsAllInfo extends Application_Model_Base
{
    protected $_sql = '';

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->_sql;
    }

    public function searchPaymentsAllInfo($startDate, $endDate, $client, $for, $commission, $account_id,$collector_id,$file_reference)
    {
        $add_query = $this->getAddQuery($client, $for, $commission, $account_id,$collector_id,$file_reference);

        $this->_sql = "select A.*,F.COLLECTOR_CODE from FILES\$PAYMENTS_ALL_INFO A
        JOIN FILES\$FILES_ALL_INFO F ON A.FILE_ID = F.FILE_ID
        where A.CREATION_DATE>='".$this->dateDbFormat($startDate)."' and A.CREATION_DATE<='".$this->dateDbFormat($endDate)."'    $add_query order by A.CREATION_DATE DESC";
        return $this->db->get_results($this->_sql);
    }

    public function searchCountPaymentsAllInfo($startDate, $endDate, $client, $for, $commission, $account_id,$collector_id,$file_reference)
    {
        $add_query = $this->getAddQuery($client, $for, $commission, $account_id,$collector_id,$file_reference);

        return $this->db->get_var("select SUM(A.AMOUNT) from FILES\$PAYMENTS_ALL_INFO A
         JOIN FILES\$FILES F ON A.FILE_ID = F.FILE_ID where A.CREATION_DATE>='".$this->dateDbFormat($startDate)."' and A.CREATION_DATE<='".$this->dateDbFormat($endDate)."' $add_query");
    }

    protected function getAddQuery($client, $for, $commission, $account_id,$collector_id,$file_reference)
    {
        $add_query = '';

        if (!empty($client)) {
            $add_query .= ' AND A.CLIENT_ID = \'' . $client . '\'';
        }
        if ($for != '-1') {
            $add_query .= ' AND A.PAYMENT_FOR = \'' . $for . '\'';
        }
        if ($commission != '-1') {
            $add_query .= ' AND A.WITH_COMMISSION = \'' . $commission . '\'';
        }
        if (!empty($account_id)) {
            $add_query .= ' AND A.ACCOUNT_ID = \'' . $account_id . '\'';
        }
        if (!empty($collector_id)) {
            $add_query .= ' AND F.COLLECTOR_ID = \'' . $collector_id . '\'';
        }
        if (!empty($file_reference)) {
            $add_query .= ' AND F.REFERENCE CONTAINING \'' . $file_reference . '\'';
        }

        return $add_query;
    }

    protected function dateDbFormat($date)
    {
        $functions = new Application_Model_CommonFunctions();
        return $functions->date_dbformat($date);
    }
}

?>
