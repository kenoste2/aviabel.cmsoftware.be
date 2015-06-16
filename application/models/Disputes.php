<?php

require_once 'application/models/Base.php';

class Application_Model_Disputes extends Application_Model_Base {

    public function countForToday() {
        $sql = "SELECT COUNT(*) FROM FILES\$REFERENCES r
                WHERE r.DISPUTE_DATE = CURRENT_DATE AND r.DISPUTE = 1";
        return $this->db->get_var($sql);
    }

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

        $queryParts []= "r.DISPUTE = 1";

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
                $escFrom = $this->db->escape($values['from']);
                $dbFrom = $this->functions->date_dbformat($escFrom);
                $queryParts []= " r.{$dateColumn} >= '{$dbFrom}'";
            }
            if ($values['till']) {
                $escTill = $this->db->escape($values['till']);
                $dbTill = $this->functions->date_dbformat($escTill);
                $queryParts []= " r.{$dateColumn} <= '{$dbTill}'";
            }
        }
        return $queryParts;
    }
    public function getDisputeForReference($referenceId)
    {

        $sql = "SELECT DISPUTE_DATE,DISPUTE_ENDED_DATE,DEBTOR_DISPUTE_COMMENT,DISPUTE_COMMENT,DISPUTE_STATUS,DISPUTE_ASSIGNEE,DEBTOR_DISPUTE_EMAIL,DEBTOR_DISPUTE_PHONE,DISPUTE_AMOUNT
                FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}";
        $row = $this->db->get_row($sql);
        return $row;
    }

}




?>
