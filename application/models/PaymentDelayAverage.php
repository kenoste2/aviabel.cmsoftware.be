<?php

require_once 'application/models/Base.php';

class Application_Model_PaymentDelayAverage extends Application_Model_Base {

    public function addPaymentDelayHistory($debtorId, $paymentDelay, $nrOfPayments) {

        $sql = "SELECT COALESCE(MAX(PAYMENT_DELAY_AVERAGE_ID), 0) + 1 FROM PAYMENT_DELAY_AVERAGE";
        $newId = $this->db->get_var($sql);

        $now = new DateTime();
        $data = array(
            "PAYMENT_DELAY_AVERAGE_ID" => $newId,
            "DEBTOR_ID" => $debtorId,
            "PAYMENT_DELAY" => $paymentDelay,
            "NR_OF_PAYMENTS" => $nrOfPayments,
            "DATE_STAMP" => $now->format("Y-m-d")
        );
        $this->saveData("PAYMENT_DELAY_AVERAGE", $data);
    }

    public function getPaymentForecast($clientId = null) {
        $where = "";
        if($clientId) {
            $where = "AND F.CLIENT_ID = {$clientId}";
        }

        $sql = "SELECT FORECAST_DAY, COUNT(*) AS FORECAST_VALUE
                FROM (
                    SELECT DATEADD(DAY,
                                  (SELECT FIRST 1 PAYMENT_DELAY
                                  FROM PAYMENT_DELAY_AVERAGE
                                  WHERE DEBTOR_ID = F.DEBTOR_ID
                                  ORDER BY DATE_STAMP DESC),
                                  R.INVOICE_DATE) AS FORECAST_DAY
                    FROM FILES\$REFERENCES R
                        JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID
                    WHERE R.SALDO_AMOUNT > 0 AND R.AMOUNT > 0 {$where}
                )
                WHERE FORECAST_DAY > CURRENT_DATE AND FORECAST_DAY <= CURRENT_DATE + 60
                GROUP BY FORECAST_DAY
                ORDER BY FORECAST_DAY ASC";
        $results = $this->db->get_results($sql);
        if(count($results) <= 0) {
            //NOTE: when no data available: return empty information for the next seven days.
            $emptySet = array();
            for($i = 0; $i < 7; $i++) {
                $now = new DateTime();
                $now->add(new DateInterval("P{$i}D"));
                $emptySet []= (object) array( "FORECAST_DAY" => $now->format("Y-m-d"), "FORECAST_VALUE" => 0);
            }
            return $emptySet;
        }

        $correctedSet = array();

        $firstDaySet = false;
        foreach($results as $result) {
            $dayToReach = DateTime::createFromFormat('Y-m-d', $result->FORECAST_DAY);

            //NOTE: if the first day is not set, add an empty item for that day so that it shows up in graphs.
            if(!$firstDaySet) {
                $now = new DateTime();
                $interval = $now->diff($dayToReach);
                if($interval->days >= 1) {
                    $correctedSet []= (object) array(
                        "FORECAST_DAY" =>  $now->format('Y-m-d'),
                        "FORECAST_VALUE" => 0
                    );
                }
                $firstDaySet = true;
            }

            $correctedSet []= (object) array(
                "FORECAST_DAY" =>  $result->FORECAST_DAY,
                "FORECAST_VALUE" => $result->FORECAST_VALUE);
        }
        return $correctedSet;
    }
}

?>
