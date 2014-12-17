<?php

require_once 'application/models/Base.php';

class Application_Model_TempPayments extends Application_Model_Base
{
    public function getUntreatedTempPayments()
    {
        return $this->db->get_results("select * from TEMP_PAYMENTS where TREATED = 0");
    }

    public function getTempPayment($id)
    {
        return $this->db->get_row("select * from TEMP_PAYMENTS where PAYMENT_ID = '$id'");
    }

    public function add($data)
    {
        $data['CREATED'] = date('Y-m-d');
        $data['CREATEDBY'] = $this->online_user;

        return $this->addData("TEMP_PAYMENTS", $data);
    }

    public function delete($id)
    {
        return $this->db->query("update TEMP_PAYMENTS set TREATED='1' where PAYMENT_ID='$id'");
    }
}

?>
