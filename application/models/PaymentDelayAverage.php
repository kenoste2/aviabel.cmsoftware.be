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
        print_r($data);
        $this->saveData("PAYMENT_DELAY_AVERAGE", $data);
    }

    public function getPaymentForecast() {
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
                    WHERE (SELECT SUM(AMOUNT) FROM FILES\$PAYMENTS WHERE REFERENCE_ID = R.REFERENCE_ID) < R.SALDO_AMOUNT
                )
                WHERE FORECAST_DAY > CURRENT_DATE
                GROUP BY FORECAST_DAY";
        return $this->db->get_results($sql);
    }
}

?>
