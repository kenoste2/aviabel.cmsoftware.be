<?php

require_once 'application/models/Base.php';

class Application_Model_Disputes extends Application_Model_Base {
    public function search($searchArray) {

        $queryParts = array();
        foreach($searchArray as $key => $values) {
            if($key === 'DISPUTE_STATUS_ID') {
                $escDisputeStatusId = $this->db->escape($values);
                $queryParts += "r.DISPUTE_STATUS_ID = {$escDisputeStatusId}"; //TODO: change database structure to use ids instead of varchars
            }

            if($key === 'DISPUTE_OWNER') {
                $escDisputeOwner = $this->db->escape($values);
                $queryParts += "r.DISPUTE_ASSIGNEE = '{$escDisputeOwner}'";
            }

            $queryParts = $this->addDateRangePart($key, 'DATE_STARTED', $values, "DISPUTE_DATE", $queryParts);
            $queryParts = $this->addDateRangePart($key, 'DATE_ENDED', $values, "DISPUTE_ENDED_DATE", $queryParts);
            $queryParts = $this->addDateRangePart($key, 'EXPIRY_DATE', $values, "DISPUTE_DUE_DATE", $queryParts);


        }
        $extendedWhere = implode(" AND ", $queryParts);
        return $this->db->get_results("SELECT * FROM FILES\$REFERENCES WHERE {$$extendedWhere}");
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
                $queryParts += "r.{$dateColumn} => {$escDateStartedFrom}";
            }
            if ($values['till']) {
                $escDateStartedTill = $this->db->escape($values['till']);
                $queryParts += "r.{$dateColumn} <= {$escDateStartedTill}";
                return $queryParts;
            }
            return $queryParts;
        }
        return $queryParts;
    }
}

?>
