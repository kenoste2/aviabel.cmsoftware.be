<?php

require_once 'application/models/Base.php';

class Application_Model_FilesPayments extends Application_Model_Base {

    protected $_transactionId = 0;

    /**
     * @return int
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }

    public function delete ($id) {
        $sql = "DELETE FROM FILES\$PAYMENTS WHERE PAYMENT_ID = {$id}";
        $this->db->query($sql);
    }


    public function save($data,$where) {
        $this->saveData('FILES$PAYMENTS', $data, $where);
    }

    public function getAmountSumByFileAndDate($fileId, $date)
    {
        $this->db->get_var("SELECT SUM(AMOUNT) FROM FILES\$PAYMENTS WHERE FILE_ID = $fileId AND CREATION_DATE = '$date'");
    }

    public function addPayment($fileId, $amount, $accountId, $valutaDate = false, $description = false, $referenceId = false) {

        global $config;

        if (empty($valutaDate)) {
            $valutaDate = date("Y-m-d");
        }

        $journalModel = new Application_Model_Journal();
        $journalId = $journalModel->add(array(
            "ACCOUNT_ID" => $accountId,
            "AMOUNT" => $amount,
            "FILE_ID" => $fileId,
            "VALUTA_DATE" => $valutaDate,
            "DESCRIPTION" => $description,
        ));
        $this->_transactionId = $journalModel->getTransactionId();


        $sql = "DELETE FROM FILES\$PAYMENTS WHERE JOURNAL_ID=$journalId";
        $this->db->query($sql);

        $bookOrder = explode("-", $config->bookorder);

        $restAmount = $amount;

        if ($amount > 0.00) {

            foreach ($bookOrder as $paymentFor) {
                if ($restAmount > 0.00) {
                    $restAmount = $this->_addAmount($journalId, $paymentFor, $referenceId, $restAmount, $valutaDate);
                }
            }

            if (!empty($restAmount) && $restAmount > 0.00) {
                $this->_addToMuch($journalId, $restAmount, $valutaDate);
            }
        }

        if ($amount < 0.00) {

            $payedTomuch = $this->db->get_var("SELECT PAYED_UNKNOWN FROM FILES\$FILES WHERE FILE_ID = {$fileId}");

            if ($payedTomuch > 0.00) {
                $restAmount = $this->_addToMuch($journalId, $restAmount, $valutaDate);
            }

            $bookOrder = array_reverse($bookOrder);


            foreach ($bookOrder as $paymentFor) {
                if (!empty($restAmount) && $restAmount <= 0.00000) {
                    $restAmount = $this->_addAmount($journalId, $paymentFor, $referenceId, $restAmount, $valutaDate);
                }
            }
        }
        $refObj = new Application_Model_FilesReferences();
        $refObj->closeReferencesFromFileIfPayed($fileId);

        $fileObj = new Application_Model_Files();
        $fileObj->setHighestState($fileId);

    }

    private function _addAmount($journalId, $paymentFor, $referenceId, $amount, $valutaDate) {

        $restAmount = $amount;


        if ($paymentFor == "INTERESTS") {
            $paymentFor = "INTEREST";
        }
        $referenceBaseAmount = $this->db->get_var("SELECT {$paymentFor} FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}");

        if ($amount > 0.00 or ($referenceBaseAmount < 0.00 && $amount < 0.00) ) {
            $payedOrSaldo = "SALDO";
        } else {
            $payedOrSaldo = "PAYED";
        }

        switch ($paymentFor) {
            case "AMOUNT" :
                $saldoString = "{$payedOrSaldo}_AMOUNT";
                $forType = "A";
                break;
            case "INTEREST" :
                $saldoString = "{$payedOrSaldo}_INTEREST";
                $forType = "I";
                break;
            case "COSTS" :
                $saldoString = "{$payedOrSaldo}_COSTS";
                $forType = "C";
                break;
        }

        $fileId = $this->db->get_var("SELECT FILE_ID FROM ACCOUNTS\$JOURNAL WHERE JOURNAL_ID = $journalId");
        $linkedAmount = 0;
        if (!empty($referenceId)) {
            $referenceAmount = $this->db->get_var("SELECT {$saldoString} FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}");


            if (abs($amount) >= $referenceAmount) {
                if ($amount > 0.00 or ($referenceBaseAmount < 0.00 && $amount < 0.00)) {
                    $amountToLink = $referenceAmount;
                } else {
                    $amountToLink = $referenceAmount * -1;
                }
            } else {
                $amountToLink = $amount;
            }

            if ($amountToLink <> 0) {

                $data = array(
                    "FILE_ID" => $fileId,
                    "JOURNAL_ID" => $journalId,
                    "REFERENCE_ID" => $referenceId,
                    "AMOUNT" => $amountToLink,
                    "PAYMENT_FOR" => $forType,
                    "PAYMENT_DATE" => $valutaDate,
                );
                $this->saveData('FILES$PAYMENTS', $data);
                $linkedAmount += $amountToLink;
                $restAmount = $amount - $amountToLink;
            }
        } else {

            $restAmount = $amount;
            $references = $this->db->get_results("SELECT {$saldoString} AS SALDO,REFERENCE_ID FROM FILES\$REFERENCES WHERE FILE_ID = $fileId ORDER BY INVOICE_DATE,REFERENCE");
            if (!empty($references)) {
                foreach ($references as $reference) {

                    if (!empty($restAmount) && abs($restAmount) > 0.00) {

                        $saldoAmount = $reference->SALDO;

                        if (abs($restAmount) >= $saldoAmount) {
                            if ($amount > 0.00) {
                                $amountToLink = $saldoAmount;
                            } else {
                                $amountToLink = $saldoAmount * -1;
                            }
                        } else {
                            $amountToLink = $restAmount;
                        }
                        if ($amountToLink <> 0) {
                            $data = array(
                                "FILE_ID" => $fileId,
                                "JOURNAL_ID" => $journalId,
                                "AMOUNT" => $amountToLink,
                                "PAYMENT_FOR" => $forType,
                                "PAYMENT_DATE" => $valutaDate,
                                "REFERENCE_ID" => $reference->REFERENCE_ID,
                            );
                            $this->saveData('FILES$PAYMENTS', $data);
                            $linkedAmount += $amountToLink;

                            $restAmount = $amount - $linkedAmount;
                        }
                    }
                }
            }
        }
        return $restAmount;
    }

    private function _addToMuch($journalId, $restAmount, $valutaDate) {

        $fileId = $this->db->get_var("SELECT FILE_ID FROM ACCOUNTS\$JOURNAL WHERE JOURNAL_ID = $journalId");

        if ($restAmount > 0.01) {

            $data = array(
                "FILE_ID" => $fileId,
                "JOURNAL_ID" => $journalId,
                "AMOUNT" => $restAmount,
                "PAYMENT_FOR" => '?',
                "PAYMENT_DATE" => $valutaDate,
            );
            $this->saveData('FILES$PAYMENTS', $data);

            return 0.00;
        }
        if ($restAmount < -0.01) {

            $payedTomuch = $this->db->get_var("SELECT PAYED_UNKNOWN FROM FILES\$FILES WHERE FILE_ID = {$fileId}");

            if (abs($restAmount) > $payedTomuch) {
                $amountToLink = $payedTomuch * -1;
            } else {
                $amountToLink = $restAmount;
            }

            $data = array(
                "FILE_ID" => $fileId,
                "JOURNAL_ID" => $journalId,
                "AMOUNT" => $amountToLink,
                "PAYMENT_FOR" => '?',
                "PAYMENT_DATE" => $valutaDate,
            );
            $this->saveData('FILES$PAYMENTS', $data);

            $return = $restAmount - $amountToLink;
            return $return;
        }
    }

    public function getDayPayments($date = false)
    {
        if (empty($date)) {
            $date = date("Y-m-d");
        }

        $sql = "SELECT SUM(AMOUNT) AS AMOUNT FROM FILES\$PAYMENTS WHERE CREATION_DATE = '{$date}'";
        $count = $this->db->get_var($sql);
        return $count;

    }


}

?>
