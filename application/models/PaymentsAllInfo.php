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

    public function searchPaymentsAllInfo($startDate, $endDate, $client, $for, $commission, $account_id)
    {
        $add_query = $this->getAddQuery($client, $for, $commission, $account_id);

        $this->_sql = "select FILE_ID,DEBTOR_NAME, FILE_NR,PAYMENT_ID, CREATION_DATE, PAYMENT_DATE,AMOUNT, PAYMENT_FOR,REFERENCE,REFUND_STATEMENT, INVOICEABLE,WITH_COMMISSION, ACCOUNT_CODE, JOURNAL_DESCRIPTION,ACCOUNT_ID,CLIENT_CODE from FILES\$PAYMENTS_ALL_INFO where CREATION_DATE>='".$this->dateDbFormat($startDate)."' and CREATION_DATE<='".$this->dateDbFormat($endDate)."'    $add_query order by CREATION_DATE DESC";
        return $this->db->get_results($this->_sql);
    }

    public function searchCountPaymentsAllInfo($startDate, $endDate, $client, $for, $commission, $account_id)
    {
        $add_query = $this->getAddQuery($client, $for, $commission, $account_id);

        return $this->db->get_var("select SUM(AMOUNT) from FILES\$PAYMENTS_ALL_INFO where CREATION_DATE>='".$this->dateDbFormat($startDate)."' and CREATION_DATE<='".$this->dateDbFormat($endDate)."' $add_query");
    }

    protected function getAddQuery($client, $for, $commission, $account_id)
    {
        $add_query = '';

        if (!empty($client)) {
            $add_query .= ' AND CLIENT_CODE = \'' . $client . '\'';
        }
        if ($for != '-1') {
            $add_query .= ' AND PAYMENT_FOR = \'' . $for . '\'';
        }
        if ($commission != '-1') {
            $add_query .= ' AND WITH_COMMISSION = \'' . $commission . '\'';
        }
        if (!empty($account_id)) {
            $add_query .= ' AND ACCOUNT_ID = \'' . $account_id . '\'';
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
