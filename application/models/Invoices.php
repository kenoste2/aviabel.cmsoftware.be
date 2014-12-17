<?php

require_once 'application/models/Base.php';

class Application_Model_Invoices extends Application_Model_Base
{
    public function getInvoicesByClient($clientId)
    {
        $sql = "select INVOICE_ID,INVOICE_NR,INVOICE_DATE,CODE,TOTAL,COMPENSATED
          from INVOICES\$INVOICES_ALL_INFO
          where CLIENT_ID=$clientId
          order by INVOICE_NR ASC";
        return $this->db->get_results($sql);
    }
}

?>
