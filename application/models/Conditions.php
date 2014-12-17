<?php

require_once 'application/models/Base.php';

class Application_Model_Conditions extends Application_Model_Base
{

    public function getConditions($clientId)
    {
        return $this->db->get_results("select RECORD_ID,FROM_DATE,CONDITION_TYPE,END_VALUE,INVOICE_PERCENT,INVOICE_MINIMUM
            from CLIENTS\$CONDITIONS where CLIENT_ID='$clientId' order by FROM_DATE");
    }

    public function getCondition($recordId)
    {
        return $this->db->get_row("SELECT * FROM CLIENTS\$CONDITIONS WHERE RECORD_ID = " . $recordId);
    }

    public function add(array $data, $clientId)
    {
        if(array_key_exists('FROM_DATE', $data) && !empty($data['FROM_DATE'])) {
            $data['FROM_DATE']  = $this->dateDbFormat($data['FROM_DATE']);
        }

        if(array_key_exists('END_VALUE', $data) && !empty($data['END_VALUE'])) {
            $data['END_VALUE']  = $this->functions->dbBedrag($data['END_VALUE']);
        }

        if(array_key_exists('INVOICE_PERCENT', $data) && !empty($data['INVOICE_PERCENT'])) {
            $data['INVOICE_PERCENT']  = $this->functions->dbBedrag($data['INVOICE_PERCENT']);
        }

        if(array_key_exists('INVOICE_MINIMUM', $data) && !empty($data['INVOICE_MINIMUM'])) {
            $data['INVOICE_MINIMUM']  = $this->functions->dbBedrag($data['INVOICE_MINIMUM']);
        }

        $data['CLIENT_ID'] = $clientId;

        return $this->addData("CLIENTS\$CONDITIONS", $data, 'RECORD_ID');
    }

    public function save(array $data, $where)
    {
        return $this->saveData("CLIENTS\$CONDITIONS", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from CLIENTS\$CONDITIONS where RECORD_ID='$id'");
    }
}

?>
