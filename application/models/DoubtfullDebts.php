<?php

require_once 'application/models/Base.php';

class Application_Model_DoubtfullDebts extends Application_Model_Base
{

    public function getDueClientFileList() {


        $sql = $this->getDueClientFileSql();
        $results = $this->db->get_results($sql);


        $resultsArray  = array();
        if (!empty($results)) {
            $counter = 0;
            foreach ($results as $row) {
                $counter++;
                $row->PROVISION1 = $row->KLASSE1*0.15;
                $row->PROVISION2 = $row->KLASSE2*0.50;
                $row->PROVISION3 = $row->KLASSE3*1;

                if ($row->PROVISION1 < 0.00) {
                    $row->PROVISION2 += $row->PROVISION1;
                    $row->PROVISION1 = 0.00;
                }
                if ($row->PROVISION2 < 0.00) {
                    $row->PROVISION3 += $row->PROVISION2;
                    $row->PROVISION2 = 0.00;
                }
                if ($row->PROVISION3 < 0.00) {
                    $row->PROVISION3 = 0.00;
                }


                $resultsArray[$counter] = $row;
            }
        }
        return $resultsArray;

    }

    public function getDueClientFileSql()
    {
        $sql = "SELECT F.FILE_ID,F.REFERENCE,F.DEBTOR_NAME,
            (SELECT SUM(SALDO_AMOUNT)FROM FILES\$REFERENCES R WHERE F.FILE_ID = R.FILE_ID AND (CURRENT_DATE-START_DATE) <= 90) AS SALDO,
            (SELECT SUM(SALDO_AMOUNT) FROM FILES\$REFERENCES R WHERE F.FILE_ID = R.FILE_ID AND (CURRENT_DATE - START_DATE) >  90 AND (CURRENT_DATE - START_DATE) <= 180) AS KLASSE1,
            (SELECT SUM(SALDO_AMOUNT) FROM FILES\$REFERENCES R WHERE F.FILE_ID = R.FILE_ID AND (CURRENT_DATE - START_DATE) >  180 AND (CURRENT_DATE - START_DATE) <= 365) AS KLASSE2,
            (SELECT SUM(SALDO_AMOUNT) FROM FILES\$REFERENCES R WHERE F.FILE_ID = R.FILE_ID  AND (CURRENT_DATE - START_DATE) >  365) AS KLASSE3   FROM FILES\$FILES_ALL_INFO F WHERE (SELECT SUM(SALDO_AMOUNT)FROM FILES\$REFERENCES R
            WHERE F.FILE_ID = R.FILE_ID AND START_DATE < CURRENT_DATE - 90) > 0.00 ORDER BY F.REFERENCE";

        return $sql;

    }

    public function getDueClientInvoicesSql() {

        $sql = "SELECT F.REFERENCE,F.DEBTOR_NAME,R.INVOICE_DATE, R.START_DATE, R.REFERENCE, R.AMOUNT ,R.SALDO_AMOUNT,
                CASE
                  WHEN ((CURRENT_DATE - START_DATE) >  90 AND (CURRENT_DATE - START_DATE) <= 180)  then 15
                  WHEN ((CURRENT_DATE - START_DATE) >  180 AND (CURRENT_DATE - START_DATE) <=365)  then 50
                  WHEN (CURRENT_DATE - START_DATE) >  365  then 100
                END,
                (SELECT SUM(SALDO_AMOUNT)FROM FILES\$REFERENCES R2 WHERE R.REFERENCE_ID = R2.REFERENCE_ID AND (CURRENT_DATE - R2.START_DATE) >  90 AND (CURRENT_DATE - R2.START_DATE) <= 180) * 0.15 AS PROVISION1,
                (SELECT SUM(SALDO_AMOUNT)FROM FILES\$REFERENCES R2 WHERE R.REFERENCE_ID = R2.REFERENCE_ID AND (CURRENT_DATE - R2.START_DATE) >  180 AND (CURRENT_DATE - R2.START_DATE) <= 365)  * 0.50 AS PROVISION2,
                (SELECT SUM(SALDO_AMOUNT)FROM FILES\$REFERENCES R2 WHERE R.REFERENCE_ID = R2.REFERENCE_ID AND (CURRENT_DATE - R2.START_DATE) >  365) AS PROVISION3
                FROM FILES\$REFERENCES R
                JOIN FILES\$FILES_ALL_INFO F ON F.FILE_ID = R.FILE_ID WHERE START_DATE < CURRENT_DATE - 90 ORDER BY F.REFERENCE";
        return $sql;

    }




}

?>
