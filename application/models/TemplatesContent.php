<?php

require_once 'application/models/Base.php';

class Application_Model_TemplatesContent extends Application_Model_Base
{

    public function getFileContent($text, $fileId, $templateId = false)
    {
        $fileObj = new Application_Model_File();
        $fileContent = $fileObj->getFileViewData($fileId);

        $templateObj = new Application_Model_Templates();
        $excludeDisputes = $templateObj->excludeDispute($templateId);



        $fields = array();

        if (!empty($fileContent)) {
            foreach ($fileContent as $field => $value) {
                if (stripos($text, "x{$field}x") !== false) {
                    $fields[$field] = $value;
                }
            }
        }

        // replace Amounts

        $fileAmounts = $fileObj->getFileAmounts($fileId, $excludeDisputes);
        if (!empty($fileAmounts)) {
            foreach ($fileAmounts as $field => $value) {
                if (stripos($text, "x{$field}x") !== false) {
                    $fields[$field] = $value;
                }
            }
        }

        return $fields;
    }
    public function getPrevActionsDates($text, $fileId)
    {
        $fileActions = new Application_Model_FilesActions();
        $actions = $fileActions->getActionsByFileId($fileId);

        $fields = array();
        if (!empty($actions)) {
            foreach ($actions as $row) {
                $field = "DATE_{$row->ACTION_CODE}";
                if (stripos($text, "x{$field}x") !== false) {
                    $fields[$field] = $this->functions->dateformat($row->ACTION_DATE);
                }
            }
        }
        return $fields;
    }

    public function getInvoices($fileId,$lang)
    {
        $fileReferencesObj = new Application_Model_FilesReferences();
        $invoices = $fileReferencesObj->getReferencesByFileId($fileId);
        $factuurnummer_c = $this->functions->T("factuurnummer_c",$lang);
        if (!empty($invoices)) {
            $text = "";
            foreach ($invoices as $invoice) {
                $text .= "\n- {$factuurnummer_c} {$invoice->REFERENCE} (" . $this->functions->dateformat($invoice->INVOICE_DATE) . ") : " . $this->functions->amount($invoice->AMOUNT) . " EUR;";
            }
        }
        return array(
            'INVOICES' => $text,
        );
    }

    public function getInvoicesDetail($fileId,$lang)
    {
        $costs_c = $this->functions->T("costs_c",$lang);
        $vervaldatum_c = $this->functions->T("vervaldatum_c",$lang);
        $total_c = $this->functions->T("total_c",$lang);
        $end_date_c = $this->functions->T("end_date_c",$lang);
        $factuurnummer_c = $this->functions->T("factuurnummer_c",$lang);

        $fileReferencesObj = new Application_Model_FilesReferences();
        $invoices = $fileReferencesObj->getReferencesByFileId($fileId);
        if (!empty($invoices)) {
            $text = "";
            foreach ($invoices as $invoice) {
                $text .= "\n- {$factuurnummer_c} {$invoice->REFERENCE} " . $this->functions->amount($invoice->AMOUNT) . " EUR , {$vervaldatum_c} : " . $this->functions->dateformat($invoice->START_DATE);
                $text .= "\n  {$end_date_c} : " . $this->functions->dateformat($invoice->START_DATE) . ", " . $this->functions->amount($invoice->INTEREST) . ' EUR';
                $text .= "\n  {$costs_c} :" . $this->functions->amount($invoice->COSTS) . ' EUR';
                $text .= "\n  <b>{$total_c} :" . $this->functions->amount($invoice->TOTAL) . ' EUR</b>';
            }
        }
        return array(
            'INVOICES_DETAILED' => $text,
        );
    }

    public function getRPV($fileId)
    {

        $fileObj = new Application_Model_File();
        $fileAmount = $fileObj->getFileField($fileId, 'AMOUNT');

        $rpvTable = $this->functions->getUserSetting('RPV_SCALE');
        $rpvTable = explode("\n", $rpvTable);
        if (!empty($rpvTable)) {
            foreach ($rpvTable as $rpv) {
                list($from, $to, $amount) = explode(";", $rpv);
                if ($fileAmount >= $from && $fileAmount <= $to) {
                    return array('RPV' => $amount);
                }
            }
        }

        return array('RPV' => 0.00);

        //return array('RPV' => $rpvTable);
        /*
        if (!empty($rpvTable)) {

            if (!empty($rpvTable)) {
                foreach ($rpvTable as $row) {
                    list($from,$to,$amount) = explode(";",$row);
                    if ($fileAmount >= $from && $fileAmount <= $to ) {
                        return array (
                            'RPV' => $amount
                        );
                    }
                }
            }
        }
        */
        return array('RPV' => 3.00);
    }


    public function getClientContent($text, $clientId)
    {
        $obj = new Application_Model_Clients();
        $content = $obj->getClientViewData($clientId);
        $fields = array();

        if (!empty($content)) {
            foreach ($content as $field => $value) {
                if (stripos($text, "x{$field}x") !== false) {
                    $fields[$field] = $value;
                }
            }
        }
        return $fields;
    }

    public function getDatesContent($text)
    {

        $fields = array();

        if (stripos($text, "xTHISDATEx") !== false) {
            $fields['THISDATE'] = date("Y-m-d");
        }

        return $fields;

    }

    public function getPaymentPlanContent($fileId, $startdate, $nrPayments)
    {

        $fileObj = new Application_Model_File();

        $openAmount = $fileObj->getFileField($fileId, "PAYABLE + INCASSOKOST");

        $fields['STARTDATE'] = $startdate;
        $fields['BPAANTAL'] = $nrPayments;

        if ($openAmount > 0.00) {
            $monthAmount = $openAmount / $nrPayments;
        } else $monthAmount = 0.00;

        $fields['BPMAANDBEDRAG'] = $monthAmount;

        return $fields;

    }
}

?>
