<?php

require_once 'application/models/Base.php';

class Application_Model_Transactions extends Application_Model_Base
{
    public function add($data)
    {
        return $this->addData("ACCOUNTS\$TRANSACTIONS", $data, 'TRANSACTION_ID');
    }
}

?>
