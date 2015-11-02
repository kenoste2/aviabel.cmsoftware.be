<?php

class Application_Model_Custom_Aviabel extends Application_Model_Base
{
    private $file = "/home/aaa/files/tripleA.csv";

    protected $reference_array = array();

    protected function _getColums()
    {

        $colums = $this->functions->getUserSetting('IMPORT_COLUMS');
        $result = array();
        $colums = explode("\n", $colums);
        if (!empty($colums)) {
            foreach ($colums as $column) {
                list($code, $value) = explode("=", $column);
                $result[trim($code)] = trim($value);
            }
        }
        return $result;
    }


    public function import() {


        $this->log(date("Y-m-d H:i")." : start import",'import');


        $this->truncate();

        $handle = $this->loadFile();
        $counter = 0;
        while (($data = fgetcsv($handle, null, ',','"')) !== false) {
            $counter++;
            if ($counter == 1) {
                continue;
            }
            $this->handleInvoiceLine($data);
        }


        $this->log(date("Y-m-d H:i")." : csv loaded",'import');

        $checkCounter = $counter-1;
        $checkImport = $this->checkImport();
        if ($checkImport) {

            $this->log(date("Y-m-d H:i")." : check OK",'import');
            $this->linkClients();
            $this->log(date("Y-m-d H:i")." : linkClients done",'import');
            $this->linkCollectors();
            $this->log(date("Y-m-d H:i")." : linkCollectors done",'import');
            $this->linkDebtors();
            $this->log(date("Y-m-d H:i")." : linkDebtors done",'import');
            $this->linkFiles();
            $this->log(date("Y-m-d H:i")." : linkFiles done",'import');
            $this->linkInvoices();
            $this->log(date("Y-m-d H:i")." : linkInvoices done",'import');
            $this->closeInvoices();
            $this->log(date("Y-m-d H:i")." : closeInvoices done",'import');
            $this->log(date("Y-m-d H:i")." : end import",'import');
            return true;
        } else {
            return false;
        }
    }

    private function checkImport() {

        $count = $this->db->get_var("SELECT COUNT(*) FROM IMPORT\$INVOICES");
        $amount = $this->db->get_var("SELECT SUM(INVOICE_AMOUNT) FROM IMPORT\$INVOICES");

        if ($amount >= 0.00 && $count>=10 ) {
            return true;
        } else {
            return false;
        }
    }


    private function loadFile(){
        $handle = fopen($this->file, "r");

        if ($handle !== false) {
            return $handle;
        } else {
            die("File {$this->file} could not be loaded.");
        }
    }


    protected function handleInvoiceLine($line)
    {

        $columns = $this->_getColums();

        $inceptionDate = trim($line[$columns['CONTRACT_INCEPTIONDATE']]);
        if ($inceptionDate == '') {
            $inceptionDate = date("Y-m-d");
        }
        $fromDate = trim($line[$columns['INVOICE_FROMDATE']]);
        if ($fromDate == '') {
            $fromDate = date("Y-m-d");
        }
        $toDate = trim($line[$columns['INVOICE_TODATE']]);
        if ($toDate == '') {
            $toDate = date("Y-m-d");
        }


        $reference = trim($line[$columns['CLIENT_NUMBER']]);
        if (!empty($line[$columns['CONTRACT_NUMBER']])) {
            $reference .= "/" . trim($line[$columns['CONTRACT_NUMBER']]);
        }




        $data = array(
            'DEVISION_CODE' => trim($line[$columns['DEVISION_CODE']]),
            'CLIENT_NUMBER' => $reference,
            'CLIENT_NAME' => trim($line[$columns['CLIENT_NAME']]),
            'CLIENT_ADDRESS' => trim($line[$columns['CLIENT_ADDRESS']]),
            'CLIENT_ZIPCODE' => trim($line[$columns['CLIENT_ZIPCODE']]),
            'CLIENT_PLACE' => trim($line[$columns['CLIENT_PLACE']]),
            'CLIENT_COUNTRY' => trim($line[$columns['CLIENT_COUNTRY']]),
            'CLIENT_LANGUAGE' => trim($line[$columns['CLIENT_LANGUAGE']]),
            'CLIENT_TEL' => trim($line[$columns['CLIENT_TEL']]),
            'CLIENT_EMAIL' => trim($line[$columns['CLIENT_EMAIL']]),
            'CLIENT_VAT' => trim($line[$columns['CLIENT_VAT']]),
            'INVOICE_AMOUNT' => trim($line[$columns['INVOICE_AMOUNT']]),
            'INVOICE_NUMBER' => trim($line[$columns['INVOICE_NUMBER']]),
            'INVOICE_DATE' => trim($line[$columns['INVOICE_DATE']]),
            'INVOICE_DUEDATE' => trim($line[$columns['INVOICE_DUEDATE']]),
            'INVOICE_TYPE' => trim($line[$columns['INVOICE_TYPE']]),
            'CREATION_DATE' => date("Y-m-d"),
            'CONTRACT_UY' => trim($line[$columns['CONTRACT_UY']]),
            'CONTRACT_INSURED' => trim($line[$columns['CONTRACT_INSURED']]),
            'CONTRACT_UNDERWRITER' => trim($line[$columns['CONTRACT_UNDERWRITER']]),
            'CONTRACT_NUMBER' => trim($line[$columns['CONTRACT_NUMBER']]),
            'VALUTA' => trim($line[$columns['VALUTA']]),
            'INVOICE_DOCCODE' => trim($line[$columns['INVOICE_DOCCODE']]),
            'INVOICE_DOCLINENUM' => trim($line[$columns['INVOICE_DOCLINENUM']]),
            'INVOICE_FROMDATE' => $fromDate,
            'INVOICE_TODATE' => $toDate,
            'INVOICE_ACNUM' => trim($line[$columns['INVOICE_ACNUM']]),
            'COLLECTOR_CODE' => trim($line[$columns['COLLECTOR_CODE']]),
            'CONTRACT_INCEPTIONDATE' => $inceptionDate,
            'CONTRACT_LINEOFBUSINESS' => trim($line[$columns['CONTRACT_LINEOFBUSINESS']]),
            'CONTRACT_LEAD' => trim($line[$columns['CONTRACT_LEAD']]),
            'LEDGER_ACCOUNT' => trim($line[$columns['LEDGER_ACCOUNT']]),
        );
        $this->addData("IMPORT\$INVOICES", $data);
        return true;
    }

    public function createCollector($code) {

        $collectorsObj = new Application_Model_Collectors();

        $data = array(
          'CODE' => $code,
          'NAME' => $code,
          'ZIP_CODE_ID' => 1,
        );

        $collectorId = $collectorsObj->add($data);

        return $collectorId;

    }


    protected function linkClients()
    {
        $clientObj = new Application_Model_Clients();

        $results = $this->db->get_results("SELECT DEVISION_CODE FROM IMPORT\$INVOICES GROUP BY DEVISION_CODE");
        foreach ($results as $row) {
            $clientId = $clientObj->getClientIdByCode($row->DEVISION_CODE);
            if (!empty($clientId)) {
                $sql = "UPDATE IMPORT\$INVOICES SET CLIENT_ID = $clientId  WHERE DEVISION_CODE = '{$row->DEVISION_CODE}' ";
                $this->db->query($sql);
            }
        }
        return true;
    }

    protected function linkCollectors()
    {
        $results = $this->db->get_results("SELECT COLLECTOR_CODE FROM IMPORT\$INVOICES GROUP BY COLLECTOR_CODE");
        foreach ($results as $row) {
            $collectorId = $this->db->get_var("SELECT COLLECTOR_ID FROM SYSTEM\$COLLECTORS WHERE CODE = '{$row->COLLECTOR_CODE}'");
            if (empty($collectorId)) {
                $collectorId = $this->createCollector($row->COLLECTOR_CODE);
            }
            if (!empty($collectorId)) {
                $sql = "UPDATE IMPORT\$INVOICES SET COLLECTOR_ID = $collectorId WHERE COLLECTOR_CODE = '{$row->COLLECTOR_CODE}' ";
                $this->db->query($sql);
            }
        }
        return true;
    }

    protected function linkDebtors()
    {
        $debtorsObj = new Application_Model_Debtors();

        $results = $this->db->get_results("SELECT CLIENT_NUMBER FROM IMPORT\$INVOICES GROUP BY CLIENT_NUMBER");
        foreach ($results as $row) {

            $reference = $row->CLIENT_NUMBER;
            if (stripos($reference,"/")!== false) {
                list($reference,$contractNumber) = explode("/",$reference);
            }


            $debtorId = $this->db->get_var("SELECT DEBTOR_ID FROM FILES\$FILES WHERE REFERENCE like  ''");
            if (empty($debtorId)) {
                $dataRow = $this->db->get_row("SELECT FIRST 1 *  FROM IMPORT\$INVOICES WHERE CLIENT_NUMBER like '{$reference}%'");

                $countryObj = new Application_Model_Countries();
                $countryId = $countryObj->getCountryByCode($dataRow->CLIENT_COUNTRY);
                if (empty($countryId)) {
                    $countryId = 4;
                }

                $languagesObj = new Application_Model_Languages();

                $trainType = $this->db->get_var("select TRAIN_TYPE  from CLIENTS\$CLIENTS WHERE CLIENT_ID = '{$dataRow->CLIENT_ID}'");
                if (empty($trainType)) {
                    $trainType = $this->functions->getUserSetting('BASE_TRAIN_TYPE');
                }


                $data = array(
                    'NAME' => $dataRow->CLIENT_NAME,
                    'ADDRESS' => $dataRow->CLIENT_ADDRESS,
                    'ZIP_CODE' => $dataRow->CLIENT_ZIPCODE,
                    'CITY' => $dataRow->CLIENT_PLACE,
                    'COUNTRY_ID' => $countryId,
                    'LANGUAGE_ID' => $languagesObj->getIdByCode($dataRow->CLIENT_LANGUAGE),
                    'E_MAIL' => $dataRow->CLIENT_EMAIL,
                    'TELEPHONE' => $dataRow->CLIENT_TEL,
                    'TELEFAX' => "",
                    'VATNR' => $dataRow->CLIENT_VAT,
                    'TRAIN_TYPE' => $trainType,
                );
                $debtorId = $debtorsObj->create($data);
            }
            $sql = "UPDATE IMPORT\$INVOICES SET DEBTOR_ID = $debtorId WHERE CLIENT_NUMBER = '{$row->CLIENT_NUMBER}' ";
            $this->db->query($sql);
        }
        return true;
    }

    protected function linkFiles()
    {
        $filesObj = new Application_Model_Files();

        $stateId = $this->functions->getUserSetting('factuur_aanmaak_status');

        $results = $this->db->get_results("SELECT CLIENT_NUMBER FROM IMPORT\$INVOICES GROUP BY CLIENT_NUMBER");
        foreach ($results as $row) {
            $fileId = $this->db->get_var("SELECT FILE_ID FROM FILES\$FILES WHERE REFERENCE = '{$row->CLIENT_NUMBER}'");
            if (empty($fileId)) {
                $dataRow = $this->db->get_row("SELECT FIRST 1 *  FROM IMPORT\$INVOICES WHERE CLIENT_NUMBER = '{$row->CLIENT_NUMBER}'");
                $data = array(
                    'FILE_NR' => $filesObj->getNextFileNr(false),
                    'CLIENT_ID' => $dataRow->CLIENT_ID,
                    'DEBTOR_ID' => $dataRow->DEBTOR_ID,
                    'REFERENCE' => $row->CLIENT_NUMBER,
                    'COLLECTOR_ID' => $dataRow->COLLECTOR_ID,
                    'STATE_ID' => $stateId,
                );
                $filesObj = new Application_Model_Files();
                $fileId = $filesObj->create($data);
            }

            $sql = "UPDATE IMPORT\$INVOICES SET FILE_ID = {$fileId} WHERE CLIENT_NUMBER = '{$row->CLIENT_NUMBER}'";
            $this->db->query($sql);
        }
    }
    protected function linkInvoices()
    {
        $referencesObj = new Application_Model_FilesReferences();
        $filesObj = new Application_Model_Files();
        $debtorsObj = new Application_Model_Debtors();

        $stateId = $this->functions->getUserSetting('factuur_aanmaak_status');

        $sql = "SELECT FILE_ID FROM IMPORT\$INVOICES GROUP BY FILE_ID";
        $results = $this->db->get_results($sql);

        foreach ($results as $row) {
            $invoicesForFile = $this->db->get_results("SELECT * FROM IMPORT\$INVOICES WHERE FILE_ID = {$row->FILE_ID} ");
            foreach ($invoicesForFile as $invoice) {

                $invoiceExists = $this->db->get_var("SELECT REFERENCE_ID FROM FILES\$REFERENCES WHERE REFERENCE = '{$invoice->INVOICE_NUMBER}' AND AMOUNT = {$invoice->INVOICE_AMOUNT} AND INVOICE_DOCCODE = '{$invoice->INVOICE_DOCCODE}' AND INVOICE_DOCLINENUM = '{$invoice->INVOICE_DOCLINENUM}'  ");
                if (empty($invoiceExists)) {
                    $debtorId = $filesObj->getDebtorId($row->FILE_ID);
                    $trainType = $debtorsObj->getTrajectType($debtorId);
                    $data = array(
                        'FILE_ID' => $invoice->FILE_ID,
                        'REFERENCE' => $invoice->INVOICE_NUMBER,
                        'AMOUNT' => $invoice->INVOICE_AMOUNT,
                        'START_DATE' => $invoice->INVOICE_DUEDATE,
                        'END_DATE' => date("Y-m-d"),
                        'REFERENCE_TYPE' => $invoice->INVOICE_TYPE,
                        'INVOICE_DATE' => $invoice->INVOICE_DATE,
                        'STATE_ID' => $stateId,
                        'DISPUTE' => 0,
                        'TRAIN_TYPE' => $trainType,
                        'CONTRACT_UY' => $invoice->CONTRACT_UY,
                        'CONTRACT_INSURED' => $invoice->CONTRACT_INSURED,
                        'CONTRACT_UNDERWRITER' => $invoice->CONTRACT_UNDERWRITER,
                        'CONTRACT_NUMBER' =>     $invoice->CONTRACT_NUMBER,
                        'VALUTA' => $invoice->VALUTA,
                        'INVOICE_DOCCODE' => $invoice->INVOICE_DOCCODE,
                        'INVOICE_DOCLINENUM' => $invoice->INVOICE_DOCLINENUM,
                        'INVOICE_FROMDATE' => $invoice->INVOICE_FROMDATE,
                        'INVOICE_TODATE' => $invoice->INVOICE_TODATE,
                        'INVOICE_ACNUM' => $invoice->INVOICE_ACNUM,
                        'COLLECTOR_CODE' => $invoice->COLLECTOR_CODE,
                        'CONTRACT_INCEPTIONDATE' => $invoice->CONTRACT_INCEPTIONDATE,
                        'CONTRACT_LINEOFBUSINESS' => $invoice->CONTRACT_LINEOFBUSINESS,
                        'CONTRACT_LEAD' => $invoice->CONTRACT_LEAD,
                        'LEDGER_ACCOUNT' => $invoice->LEDGER_ACCOUNT,
                        'LAST_IMPORT' => 1,

                    );
                    $referencesObj->create($data);
                } else {
                    $sql = "UPDATE FILES\$REFERENCES SET LAST_IMPORT = 1 WHERE REFERENCE_ID = {$invoiceExists}";
                    $this->db->query($sql);
                }
            }
        }
    }

    protected function closeInvoices()
    {
        $referenceObj = new Application_Model_FilesReferences();

        $results = $this->db->get_results("SELECT  R.REFERENCE_ID  FROM FILES\$REFERENCES R
         WHERE LAST_IMPORT = 0 AND R.STATE_ID != 40");
        if (!empty($results))
        {
            foreach ($results as $row) {
                $referenceObj->close($row->REFERENCE_ID);
            }
        }
    }


    protected function truncate()
    {
        $this->db->query("delete from IMPORT\$INVOICES");
        $this->db->query("UPDATE FILES\$REFERENCES SET LAST_IMPORT=0");
        return true;
    }


    public function getTemplateContent($text, $fileId) {
        $sql = "SELECT FIRST 1
            CONTRACT_INCEPTIONDATE, INVOICE_ACNUM, INVOICE_FROMDATE, INVOICE_TODATE, CONTRACT_NUMBER, CONTRACT_INSURED
            FROM FILES\$REFERENCES WHERE FILE_ID = {$fileId} AND CONTRACT_NUMBER != '' ORDER BY CONTRACT_INCEPTIONDATE DESC";

        $fields = array();

        $row = $this->db->get_row($sql);
        if (!empty($row)) {
            $fields['INCEPTIONDATE'] = $this->functions->dateformat($row->CONTRACT_INCEPTIONDATE);
            $fields['FROM_DATE'] = $this->functions->dateformat($row->INVOICE_FROMDATE);
            $fields['TO_DATE'] = $this->functions->dateformat($row->INVOICE_TODATE);
            $fields['CONTRACT_NUMBER'] = $row->CONTRACT_NUMBER;
            $fields['INSURED'] = $row->CONTRACT_INSURED;
        }

        return $fields;


    }


}