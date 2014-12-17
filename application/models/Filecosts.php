<?php

require_once 'application/models/Base.php';

class Application_Model_Filecosts extends Application_Model_Base
{
    protected $_sql = '';

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->_sql;
    }

    public function getSettingFilecosts()
    {
        return $this->db->get_results("select * from FILES\$COSTS");
    }

    public function getFileCosts($clientId, $data = array())
    {
        $addQuery = $this->getQuery($data);

        $this->_sql = "select FILE_NR,FILE_ID,RECORD_ID,CREATION_DATE,CODE,DESCRIPTION,AMOUNT,AMOUNT_CLIENT,
            INVOICE_NR,INVOICEABLE,EXTRA_INFO
            from FILES\$FILE_COSTS_ALL_INFO
            where CLIENT_ID = $clientId $addQuery
            order by CREATION_DATE";

        return $this->db->get_results($this->_sql);
    }

    public function getSetting($filecosts_id)
    {
        return $this->db->get_row("SELECT * FROM FILES\$COSTS WHERE COST_ID = " . $filecosts_id);
    }

    public function add($data)
    {
        if(empty($data['AMOUNT'])) {
            $data['AMOUNT'] = 0;
        }

        if(array_key_exists('AMOUNT', $data) && !empty($data['AMOUNT'])) {
            $data['AMOUNT']  = $this->functions->dbBedrag($data['AMOUNT']);
        }

        return $this->addData("FILES\$COSTS", $data);
    }

    public function save($data, $where)
    {
        if(empty($data['AMOUNT'])) {
            $data['AMOUNT'] = 0;
        }

        if(array_key_exists('AMOUNT', $data) && !empty($data['AMOUNT'])) {
            $data['AMOUNT']  = $this->functions->dbBedrag($data['AMOUNT']);
        }

        return $this->saveData("FILES\$COSTS", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from FILES\$COSTS where COST_ID='$id'");
    }

    public function getFilecostsForSelect()
    {
        return $this->db->get_results("select COST_ID, DESCRIPTION from FILES\$COSTS", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        $results = $this->db->get_results("select count(*) from FILES\$ACTIONS WHERE COST_ID = '$id'");

        if ($results[0]->COUNT > 0) {
            return false;
        } else {
            return true;
        }
    }

    protected function getQuery($data)
    {
        $addQuery = '';

        if (!empty($data['FROM_DATE'])) {
            $addQuery .= " AND CREATION_DATE >= '" . $this->dateDbFormat($data['FROM_DATE']) . "'";
        }
        if (!empty($data['UNTIL_DATE'])) {
            $addQuery .= " AND CREATION_DATE <= '" . $this->dateDbFormat($data['UNTIL_DATE']) . "'";
        }
        if ($data['COST_ID'] != '') {
            $addQuery .= " AND COST_ID = '" . $data['COST_ID'] . "'";
        }

        return $addQuery;
    }
}

?>
