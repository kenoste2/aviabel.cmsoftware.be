<?php

require_once 'application/models/Base.php';

class Application_Model_Dso extends Application_Model_Base
{
    public function getSettingDso()
    {
        return $this->db->get_results("select * from REPORTS\$DSO order by DSO_YEAR DESC, DSO_MONTH DESC");
    }

    public function getDsoList($clientId)
    {
        return $this->db->get_results("select * from REPORTS\$DSO where CLIENT_ID = {$clientId} order by DSO_YEAR, DSO_MONTH");
    }

    public function getDso($id)
    {
        return $this->db->get_row("SELECT * FROM REPORTS\$DSO WHERE DSO_ID = " . $id);
    }

    public function add($data, $clientId)
    {
        $data['CREATION_USER'] = $this->online_user;
        $data['CREATION_DATE'] = date("Y-m-d");
        $data['CLIENT_ID'] = $clientId;

        if (empty($data['DSO'])) {
            $salesLastMonth = $this->db->get_var("SELECT SALES FROM REPORTS\$DSO
                    WHERE DSO_YEAR <= '{$data['DSO_YEAR']}' AND DSO_MONTH <= '{$data['DSO_YEAR']}'
                    AND CLIENT_ID = {$clientId} ORDER BY DSO_YEAR DESC, DSO_MONTH DESC");

            $amounts = $this->getAmountsFromDb($data['DSO_YEAR'], $data['DSO_MONTH'], $clientId);
            $data['DSO'] = $this->calculateDso($salesLastMonth, $data['SALES'], $amounts->AMOUNT, $amounts->INTERCOMPANY);
        }
        return $this->addData("REPORTS\$DSO", $data);
    }

    /**
     * @param int $clientId
     * @return array
     */
    public function getIntercompany($clientId)
    {
        return $this->db->get_var("SELECT SUM(AMOUNT) FROM FILES\$REFERENCES
                                   WHERE REFERENCE_TYPE = 'INTERNATIONAL'
                                   AND FILE_ID IN (SELECT FILE_ID FROM FILES\$FILES WHERE CLIENT_ID = {$clientId})");
    }

    /**
     * @param int $clientId
     * @return array
     */
    public function getOpenAmounts($clientId)
    {
        return $this->db->get_var("SELECT SUM(SALDO_AMOUNT) FROM FILES\$REFERENCES
                                   WHERE FILE_ID IN (SELECT FILE_ID FROM FILES\$FILES WHERE CLIENT_ID = {$clientId})");
    }

    public function getAmountsFromDb($year, $month, $clientId)
    {
        return $this->db->get_row("SELECT AMOUNT,INTERCOMPANY FROM REPORTS\$SALDO
                    WHERE CREATION_DATE LIKE '{$year}-{$month}-%'
                    AND CLIENT_ID = {$clientId}
                    ORDER BY CREATION_DATE DESC");
    }

    public function save($data, $where)
    {
        return $this->saveData("REPORTS\$DSO", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("DELETE FROM REPORTS\$DSO WHERE DSO_ID='$id'");
    }

    public function getByYearMonth($year, $month)
    {
        $exists = $this->db->get_var("SELECT COUNT(*) FROM REPORTS\$DSO WHERE DSO_MONTH = '{$year}' AND DSO_YEAR = '{$month}'");

        if (empty($exists)) {
            return false;
        } else {
            return true;
        }

    }

    public function calculateDso($salesLastMonth, $salesThisMonth, $openAmounts, $intercompany) {

        $x = ($openAmounts - $salesThisMonth - $intercompany);

        if ($salesLastMonth > 0.00) {
            $dso = ($x * 30) / $salesLastMonth;
        } else {
            $dso = 0;
        }
        return round($dso,2);
    }

}

?>
