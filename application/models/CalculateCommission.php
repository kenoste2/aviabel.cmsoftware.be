<?php

require_once 'application/models/Base.php';

class Application_Model_CalculateCommission extends Application_Model_Base
{
    public function getList()
    {
        $sql = "SELECT FIRST 10 FILE_ID,PAYED_TOTAL,PAYED_COSTS,PAYED_INTEREST,PAYED_AMOUNT,DEBTOR_COUNTRY_CODE,AMOUNT,CLIENT_ID FROM FILES\$FILES_ALL_INFO WHERE DATE_CLOSED like '2014-09-%' AND PAYED_TOTAL > 0.00";
        $results = $this->db->get_results($sql);
        if (!empty($results)) {
            foreach ($results as $row) {
                print "<pre>";
                print_r($row);
                $this->calculate($row);
            }
        }
    }

    public function calculate($data)
    {
        switch ($data->DEBTOR_COUNTRY_CODE) {

            case 'BE':
            case 'NL':
            case 'LU':
                $this->calculateBenelux($data);
                break;

            case 'BG':
            case 'CY':
            case 'DK':
            case 'DE':
            case 'EE':
            case 'FI':
            case 'FR':
            case 'HU':
            case 'IE':
            case 'IT':
            case 'HR':
            case 'LT':
            case 'MT':
            case 'AT':
            case 'PL':
            case 'RO':
            case 'SI':
            case 'SK':
            case 'CZ':
            case 'GB':
            case 'SE':
                $this->calculateInternational($data,22.5,75);
                break;
            case 'GR':
            case 'PT':
            case 'ES':
                $this->calculateInternational($data,27.5,75);
                break;
        }
    }


    public function calculateInternational($data,$percent,$minimum)
    {
        $commission = ($data->PAYED_TOTAL * $percent) /100 ;

        if ($commission <= $minimum) {
            $commission = $minimum;
        }


        $paymentId = $this->db->get_var("SELECT PAYMENT_ID FROM FILES\$PAYMENTS
            WHERE FILE_ID = $data->FILE_ID
                AND PAYMENT_FOR = 'A' AND INVOICED = 0");

        $sql = "UPDATE FILES\$PAYMENTS SET COMMISSION = $commission WHERE PAYMENT_ID = {$paymentId}";
        print "<br>".$sql;
        $this->db->query($sql);


    }



    public function calculateBenelux($data)
    {

        $clientObj = new Application_Model_Clients();

        $clientObj->createClientCommission($data->CLIENT_ID);

        $invoiceMinimum = $this->db->get_var("SELECT INVOICE_MINIMUM FROM CLIENTS\$CONDITIONS WHERE CLIENT_ID = 3658 AND CONDITION_TYPE = 'C'
                ORDER BY END_VALUE");

        $sql = "UPDATE FILES\$PAYMENTS SET COMMISSION = 0 WHERE FILE_ID = {$data->FILE_ID} AND INVOICED=0";
        print "<br>".$sql;
        $this->db->query($sql);

        $costsIntrests = $data->PAYED_COSTS + $data->PAYED_INTEREST;

        if (!empty($costsIntrests)) {
            $sql = "UPDATE FILES\$PAYMENTS SET COMMISSION = AMOUNT WHERE FILE_ID = {$data->FILE_ID} AND (PAYMENT_FOR = 'I' OR PAYMENT_FOR = 'C') AND INVOICED=0";
            print "<br>".$sql;
            $this->db->query($sql);
            $sql = "SELECT SUM(COMMISSION) FROM FILES\$PAYMENTS WHERE FILE_ID = $data->FILE_ID AND (PAYMENT_FOR = 'I' OR PAYMENT_FOR = 'C')  AND INVOICED=0";
            $commissionCostsIntrests = $this->db->get_var($sql);

            $percent15 = $data->AMOUNT*0.15;
            if ($percent15 > $invoiceMinimum) {
                $minCommission = $percent15;
            } else {
                $minCommission = $invoiceMinimum;
            }

            if ($commissionCostsIntrests < $minCommission ) {
                $extraCommission = $minCommission - $commissionCostsIntrests;

                $sql = "SELECT PAYMENT_ID FROM FILES\$PAYMENTS WHERE FILE_ID = {$data->FILE_ID} AND PAYMENT_FOR = 'A'  AND INVOICED=0";
                $amountId = $this->db->get_var($sql);
                $sql = "UPDATE FILES\$PAYMENTS SET COMMISSION = $extraCommission WHERE PAYMENT_ID = {$amountId}  AND INVOICED=0 ";
                print "<br>".$sql;
                $this->db->query($sql);
            }
        } else {
            // enkel hoofdsom ontvangen
            $totalCommission = 0;
            $payedTotal = $data->PAYED_AMOUNT;

            if ($data->DEBTOR_COUNTRY_CODE == 'NL') {
                $extraCommission = 5;
            }

            $lastMax = 0;

            $paymentTotalRest = $payedTotal;

            $params = $this->db->get_results("SELECT * FROM CLIENTS\$CONDITIONS WHERE CLIENT_ID = 3658 AND CONDITION_TYPE = 'C'
                ORDER BY END_VALUE");
            if (!empty($params)) {
                foreach ($params as $param) {

                    if ($paymentTotalRest > 0.00) {
                        print "<br>$param->END_VALUE $param->INVOICE_PERCENT";

                        if ($paymentTotalRest <= $param->END_VALUE ) {
                            $thiscommission = ($paymentTotalRest - $lastMax)  * (($param->INVOICE_PERCENT + $extraCommission) / 100);
                            $totalCommission += $thiscommission;
                            print "<br>commission plus: ". $thiscommission;
                            $paymentTotalRest -= ($paymentTotalRest - $lastMax);
                        } else {
                            print "<br>commission plus: ". ($param->END_VALUE * $param->INVOICE_PERCENT) / 100;
                            $totalCommission += ($param->END_VALUE * $param->INVOICE_PERCENT) / 100;
                            $paymentTotalRest = $paymentTotalRest - $param->END_VALUE;
                        }
                    }
                }

            }


            if ($totalCommission < $invoiceMinimum) {
                $totalCommission = $invoiceMinimum;
            }

            if ($data->DEBTOR_COUNTRY_CODE == 'NL' && $totalCommission < 75.00) {
                $totalCommission = 75.00;
            }
            $sql = "SELECT PAYMENT_ID FROM FILES\$PAYMENTS WHERE FILE_ID = {$data->FILE_ID} AND PAYMENT_FOR = 'A'";
            $amountId = $this->db->get_var($sql);
            $sql = "UPDATE FILES\$PAYMENTS SET COMMISSION = $totalCommission WHERE PAYMENT_ID = $amountId ";
            print "<br>".$sql;
            $this->db->query($sql);
        }
    }

}

?>
