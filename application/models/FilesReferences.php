<?php

require_once 'application/models/Base.php';

class Application_Model_FilesReferences extends Application_Model_Base {

    public function getReferenceByReferenceAndFile($reference, $fileId)
    {
        return $this->db->get_row("select REFERENCE_ID,(SALDO_AMOUNT+SALDO_COSTS+SALDO_INTEREST) AS SALDO from FILES\$REFERENCES where REFERENCE = '$reference' AND FILE_ID = $fileId ORDER BY START_DATE");
    }

    public function saveReference($reference) {
        $data = (Array) $reference;
        $where = '';
        if(isset($reference['REFERENCE_ID'])) {
            $escReferenceId = $this->db->escape($reference['REFERENCE_ID']);
            $where = "REFERENCE_ID = {$escReferenceId}";
        }

        $data = $this->clearEmptyValues($data);

        $this->saveData('FILES$REFERENCES', $data, $where);
    }

    public function create($data) {
        $fileObj = new Application_Model_File();
        $conditions = $fileObj->getClientConditions($data['FILE_ID']);
        $data = array_merge($data, $conditions);
        
        if (empty($data['REFERENCE_TYPE'])) {
            $data['REFERENCE_TYPE'] = "FACTUUR";
        }

        if ($data['AUTO_CALCULATE'] == 1) {
            $data['INTEREST'] = $this->calculateIntrest($data['START_DATE'], $data['END_DATE']
                    , $data['AMOUNT'], $data['INTEREST_PERCENT'], $data['INTEREST_MINIMUM']);
            if($data['END_DATE'] >= $data['START_DATE'] ) {
                $data['COSTS'] = $this->calculateCost($data['AMOUNT'], $data['COST_PERCENT'], $data['COST_MINIMUM']);
            } else {
                $data['COSTS'] = 0;
            }
        }
        $referenceId = $this->addData('FILES$REFERENCES', $data);
        return $referenceId;
    }

    /**
     * @param String[] $referenceNrs
     */
    public function switchDisputeOff($referenceNrs) {
        if(count($referenceNrs) <= 0) {
            return;
        }

        $escapedReferenceNrs = array();
        foreach($referenceNrs as $refNr) {
            $escapedReferenceNrs []= "'{$this->db->escape($refNr)}'";
        }

        $referenceNrsStr = implode(',', $escapedReferenceNrs);

        $sql = "UPDATE FILES\$REFERENCES SET DISPUTE = 0 WHERE REFERENCE NOT IN ({$referenceNrsStr})";
        $this->db->query($sql);
    }

    public function update($data) {
        $fileObj = new Application_Model_File();

        if ($data['AUTO_CALCULATE'] == 1) {
            $data['INTEREST'] = $this->calculateIntrest($data['START_DATE'], $data['END_DATE']
                    , $data['AMOUNT'], $data['INTEREST_PERCENT'], $data['INTEREST_MINIMUM']);
            if($data['END_DATE'] >= $data['START_DATE'] ) {
                $data['COSTS'] = $this->calculateCost($data['AMOUNT'], $data['COST_PERCENT'], $data['COST_MINIMUM']);
            } else {
                $data['COSTS'] = 0;
            }
        }


        $data = $this->clearEmptyValues($data);

        $referenceId = $this->saveData('FILES$REFERENCES', $data, "REFERENCE_ID = {$data['REFERENCE_ID']}");
        return true;
    }
    
    public function delete($id) {
        $this->db->query("DELETE FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$id}");
        return true;
    }
    

    function calculateIntrest($date1, $date2, $amount, $percent, $minimum) {
        $intrestBedrag = 0;

        if ($amount > 0.00 && $percent > 0) {
            $interval = $this->functions->date_diff($date1, $date2);
            if ($interval > 1) {
                $intrestBedrag = ( $interval / 365) * ($percent / 100) * $amount;
            }
            if ($intrestBedrag < $minimum) {
                $intrestBedrag = $minimum;
            }
        }
        if ($intrestBedrag<0) {
            $intrestBedrag = 0;
        }
        return $intrestBedrag;
    }

    function calculateCost($amount, $percent, $minimum) {
        $costBedrag = 0;

        if ($amount > 0.00 && $percent > 0) {
            $costBedrag = ($percent / 100) * $amount;

            if ($costBedrag < $minimum) {
                $costBedrag = $minimum;
            }
        }

        if ($costBedrag<0) {
            $costBedrag = 0;
        }

        return $costBedrag;
    }

    public function getReferenceByReferenceName($referenceName) {
        $escapedReferenceName = $this->db->escape($referenceName);
        $sql = "select *
                from FILES\$REFERENCES
                where REFERENCE='{$escapedReferenceName}'";
        return $this->db->get_row($sql, "ARRAY_A");
    }

    public function getAllReferencesByFileIdAsArray($fileId) {

        $escFileId = $this->db->escape($fileId);
        $sql = "select REFERENCE_ID,REFERENCE,CREATION_DATE,START_DATE,END_DATE,INVOICE_DATE,REFUND_STATEMENT,AUTO_CALCULATE,INTEREST_PERCENT,INTEREST_MINIMUM,COST_PERCENT,COST_MINIMUM,AMOUNT,COSTS
                ,INTEREST,(AMOUNT+INTEREST+COSTS) as TOTAL,(SALDO_AMOUNT+SALDO_INTEREST+SALDO_COSTS) as SALDO, DISPUTE,I.STATE_ID, S.CODE AS STATE_CODE, I.TRAIN_TYPE from FILES\$REFERENCES I
                 JOIN FILES\$STATES S ON S.STATE_ID = I.STATE_ID
                 where FILE_ID='{$escFileId}'
                 order by START_DATE DESC ,REFERENCE_ID DESC";

        return $this->db->get_results($sql, "ARRAY_N");
    }

    public function getReferencesByFileId($fileId, $excludeDisputes = false , $due = 'A', $valuta = false)
    {
        $disputeExtra = $excludeDisputes ? "AND DISPUTE = 0" : "";

        switch ($due) {
            case 'A':
                $dueExtra = "";
                break;
            case 'N':
                $dueExtra = "AND START_DATE > CURRENT_DATE";
                break;
            case 'Y':
                $dueExtra = "AND START_DATE <= CURRENT_DATE";
                break;
        }

        if (!empty($valuta)) {
            $valutaExtra = " AND VALUTA = '{$valuta}'";
        } else {
            $valutaExtra = "";
        }

        $sql = "select I.*,(AMOUNT+INTEREST+COSTS) as TOTAL,(SALDO_AMOUNT+SALDO_INTEREST+SALDO_COSTS) as SALDO, DISPUTE,I.STATE_ID, S.CODE AS STATE_CODE, I.TRAIN_TYPE, I.VALUTA, I.INVOICE_DOCCODE, I.INVOICE_DOCLINENUM, I.INVOICE_FROMDATE, I.INVOICE_TODATE from FILES\$REFERENCES I
                 JOIN FILES\$STATES S ON S.STATE_ID = I.STATE_ID
                 where FILE_ID='{$fileId}' {$disputeExtra} {$dueExtra} {$valutaExtra}
                 order by START_DATE DESC ,REFERENCE_ID DESC";

        $results = $this->db->get_results($sql);
        return $results;
    }


    public function getAllOpenReferencesByDebtorId($debtorId) {

        $escDebtorId = $this->db->escape($debtorId);

        $sql = "select REFERENCE_ID,REFERENCE,FILE_ID,CREATION_DATE,START_DATE,END_DATE,INVOICE_DATE,REFUND_STATEMENT,AUTO_CALCULATE,INTEREST_PERCENT,INTEREST_MINIMUM,COST_PERCENT,COST_MINIMUM,AMOUNT,COSTS,
                INTEREST,(AMOUNT+INTEREST+COSTS) as TOTAL,(SALDO_AMOUNT+SALDO_INTEREST+SALDO_COSTS) as SALDO, DISPUTE,I.STATE_ID, S.CODE AS STATE_CODE, I.TRAIN_TYPE, DEBTOR_DISPUTE_COMMENT,
                DEBTOR_DISPUTE_PHONE, DEBTOR_DISPUTE_EMAIL, DISPUTE_STATUS
                from FILES\$REFERENCES I
                 JOIN FILES\$STATES S ON S.STATE_ID = I.STATE_ID
                 where FILE_ID IN (SELECT FILE_ID FROM FILES\$FILES WHERE DEBTOR_ID = {$escDebtorId})
                      AND I.STATE_ID != 40
                 order by START_DATE DESC ,REFERENCE_ID DESC";

        $results = $this->db->get_results($sql);
        return $results;

    }


    public function getTotalPastDue() {

        $sql = "SELECT SUM(AMOUNT+INTEREST+COSTS) FROM FILES\$REFERENCES
            WHERE START_DATE <= CURRENT_DATE
            AND FILE_ID IN (SELECT FILE_ID FROM FILES\$FILES WHERE DATE_CLOSED IS NULL)
            AND DISPUTE != 1";
        $value = $this->db->get_var($sql);
        if (empty($value)) {
            $value = 0.00;
        }
        return $value;
    }

    public function getTotalNotDue() {

        $sql = "SELECT SUM(AMOUNT+INTEREST+COSTS) FROM FILES\$REFERENCES
            WHERE START_DATE > CURRENT_DATE
            AND FILE_ID IN (SELECT FILE_ID FROM FILES\$FILES WHERE DATE_CLOSED IS NULL)
            AND DISPUTE != 1";

        $value = $this->db->get_var($sql);
        if (empty($value)) {
            $value = 0.00;
        }
        return $value;

    }

    public function getOpenReferencesWithAutoCalculate()
    {
        $sql = "SELECT R.REFERENCE_ID,R.START_DATE,R.AMOUNT,R.INTEREST_PERCENT,R.INTEREST_MINIMUM FROM FILES\$REFERENCES R
          JOIN FILES\$FILES F ON R.FILE_ID = F.FILE_ID
          WHERE F.DATE_CLOSED IS NULL AND R.AUTO_CALCULATE =1";

        $results = $this->db->get_results($sql);

        return $results;


    }


    public function getReferenceTypes($clientId = false) {

        if ($clientId != false) {
            $clientQuery = "AND F.CLIENT_ID = {$clientId}";
        }

        $sql = "SELECT REFERENCE_TYPE FROM FILES\$REFERENCES R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID
                WHERE 1=1  {$clientQuery} GROUP BY R.REFERENCE_TYPE ";



        $results = $this->db->get_results($sql);

        return $results;

    }


    public function setNextOrderCycle($referenceId, $maxOrderCycle = false)
    {
        $trainObj = new Application_Model_Train();
        $stateId = $this->db->get_var("SELECT STATE_ID FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}");
        $trajectType = $this->db->get_var("SELECT TRAIN_TYPE FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}");

        $currentCycle = $trainObj->getOrderCycleByState($stateId, $trajectType);
        $nextCycle = $currentCycle+1;


        if (!empty($maxOrderCycle)) {
            if ($nextCycle > $maxOrderCycle) {
                $nextCycle = $maxOrderCycle;
            }
        }

        $newStateId = $trainObj->getStateIdCycleByOrderCycle($nextCycle, $trajectType);


        if (!empty($newStateId)) {
            $data = array(
                'STATE_ID' => $newStateId,
                'REFERENCE_ID' => $referenceId,
            );

            $this->update($data);

        }
        return true;

    }

    public function getOldestReferenceFromFile($fileId)
    {
        return $this->db->get_row("SELECT * FROM FILES\$REFERENCES
                WHERE FILE_ID= {$fileId}
                AND DISPUTE = 0
                AND STATE_ID != 40
                ORDER BY START_DATE ASC");
    }


    public function closeReferencesFromFileIfPayed($fileId)
    {
        $sql = "SELECT REFERENCE_ID FROM FILES\$REFERENCES WHERE (SALDO_AMOUNT + SALDO_INTEREST + SALDO_COSTS) <= 0.00 AND FILE_ID = {$fileId}";
        $results = $this->db->get_results($sql);
        if ($results) {
            foreach ($results as $row) {
                $sql = "UPDATE FILES\$REFERENCES SET STATE_ID = 40 WHERE REFERENCE_ID = {$row->REFERENCE_ID}";
                $this->db->query($sql);
            }
        }

    }

    public function getFileAmountsByValute($fileId) {
        $sql = "SELECT VALUTA, SUM(AMOUNT) AS AMOUNT, SUM(PAYED_AMOUNT) AS PAYED_AMOUNT, SUM(SALDO_AMOUNT) AS SALDO_AMOUNT
                FROM FILES\$REFERENCES WHERE FILE_ID = {$fileId} GROUP BY VALUTA";
        $results = $this->db->get_results($sql);
        return $results;
    }

    public function getFileReferenceValutas($fileId) {
        $sql = "SELECT VALUTA
                FROM FILES\$REFERENCES WHERE FILE_ID = {$fileId} GROUP BY VALUTA";
        $results = $this->db->get_results($sql);
        return $results;
    }






    public function close($referenceId) {

        $paymentsObj = new Application_Model_FilesPayments();
        $accountsObj = new Application_Model_Accounts();
        $internalAccount = $accountsObj->getInternalAccountId();


        $sql =  "SELECT FILE_ID,REFERENCE,(SALDO_AMOUNT + SALDO_INTEREST + SALDO_COSTS) AS SALDO FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}";
        $row = $this->db->get_row($sql);
        $paymentsObj->addPayment($row->FILE_ID,$row->SALDO,$internalAccount,false,$row->REFERENCE,$referenceId);

        $sql = "UPDATE FILES\$REFERENCES SET STATE_ID = 40 WHERE REFERENCE_ID = {$referenceId}";
        $this->db->query($sql);
    }


}

?>
