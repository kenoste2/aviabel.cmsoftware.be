<?php

require_once 'application/models/Base.php';

class Application_Model_TempImport extends Application_Model_Base
{
    public function add($data)
    {
        return $this->addData("IMPORT\$INVOICES", $data);
    }
    public function truncate()
    {
        $this->db->query("delete from IMPORT\$INVOICES");
    }

    public function getUnlinkedClients()
    {


    }
}

?>
