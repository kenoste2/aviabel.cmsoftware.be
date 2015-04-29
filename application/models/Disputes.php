<?php

require_once 'application/models/Base.php';

class Application_Model_Disputes extends Application_Model_Base {
    public function search($searchArray) {

        $queryParts = array();
        foreach($searchArray as $key => $values) {
            if($key === 'DISPUTE_STATUS') {
                $escDisputeStatus = $this->db->escape($values);
                $queryParts []= " r.DISPUTE_STATUS = '{$escDisputeStatus}'";
            }

            if($key === 'DISPUTE_ASSIGNEE') {
                $escDisputeOwner = $this->db->escape($values);
                $queryParts []= " r.DISPUTE_ASSIGNEE = '{$escDisputeOwner}'";
            }

            $queryParts = $this->addDateRangePart($key, 'DATE_STARTED', $values, "DISPUTE_DATE", $queryParts);
            $queryParts = $this->addDateRangePart($key, 'DATE_ENDED', $values, "DISPUTE_ENDED_DATE", $queryParts);
            $queryParts = $this->addDateRangePart($key, 'EXPIRY_DATE', $values, "DISPUTE_DUEDATE", $queryParts);
        }

        if(count($queryParts) <= 0)  {
            $queryParts []= "1 = 1";
        }

        $extendedWhere = implode(" AND ", $queryParts);
        $sql = "SELECT r.*,
                    (SELECT d.NAME FROM FILES\$DEBTORS d
                     WHERE d.DEBTOR_ID IN
                        (SELECT f.DEBTOR_ID FROM FILES\$FILES f
                         WHERE f.FILE_ID = r.FILE_ID)) AS DEBTOR_NAME,
                    (SELECT f.REFERENCE FROM FILES\$FILES f
                     WHERE f.FILE_ID = r.FILE_ID) AS DEBTOR_NUMBER
                FROM FILES\$REFERENCES r WHERE {$extendedWhere}";
        return $this->db->get_results($sql);
    }

    /**
     * @param $key
     * @param $dateKey
     * @param $values
     * @param $dateColumn
     * @param $queryParts
     * @return string
     */
    public function addDateRangePart($key, $dateKey, $values, $dateColumn, $queryParts)
    {
        if ($key === $dateKey) {
            if ($values['from']) {
                $escDateStartedFrom = $this->db->escape($values['from']);
                $queryParts []= " r.{$dateColumn} >= '{$escDateStartedFrom}'";
            }
            if ($values['till']) {
                $escDateStartedTill = $this->db->escape($values['till']);
                $queryParts []= " r.{$dateColumn} <= '{$escDateStartedTill}'";
            }
        }
        return $queryParts;
    }
}

?>
