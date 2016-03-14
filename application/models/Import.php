<?php

class Application_Model_Import extends Application_Model_Base
{

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

    public function processFileFullPath($file, $path)
    {
        $tmpName = $file['tmp_name'];
        $name = $file['name'];
        $newFileName = $path . DIRECTORY_SEPARATOR . $name;
        copy($tmpName, $newFileName);
        unlink($tmpName);
        return $newFileName;
    }

    public function processFile($file, $dir)
    {
        $tmpName = $file['tmp_name'];
        $name = $file['name'];
        $path = APPLICATION_PATH . $dir;

        $newFileName = $path . DIRECTORY_SEPARATOR . $name;
        move_uploaded_file($tmpName, $newFileName);

        return $newFileName;
    }

    public function importPaymentCsv($fileName)
    {


        $handle = fopen($fileName, "r");

        $fileCount = 0;
        while (($data = fgetcsv($handle, null, ';')) !== false) {
            if ($this->handlePaymentLine($data, $fileName)) {
                $fileCount++;
            }
        }

        return $fileCount;
    }

    public function importFileCsv($fileName)
    {

        $tempImportModel = new Application_Model_TempImport();

        $this->truncate();


        $handle = fopen($fileName, "r");
        $counter = 0;
        while (($data = fgetcsv($handle, null, ';')) !== false) {
            $counter++;
            if ($counter == 1) {
                continue;
            }
            $this->handleInvoiceLine($data, $fileName);
        }


        $this->linkClients();
        $this->linkDebtors();
        $this->linkFiles();
        $this->linkInvoices();
        $this->CloseInvoices();
        return $counter;
    }

    public function importRatesCsv($filePath)
    {
        $file = fopen($filePath, 'r');
        $importedRates = array();

        $header = null;
        while ($row = fgetcsv($file)) {
            if ($header === null) {
                $header = $row;
                continue;
            }
            $importedRates[] = array_combine($header, $row);
        }
        fclose($file);

        return $importedRates;
    }


    protected function truncate()
    {
        $this->db->query("delete from IMPORT\$INVOICES");
    }


    protected function linkFiles()
    {
        $filesObj = new Application_Model_Files();

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

                $invoiceExists = $this->db->get_row("SELECT REFERENCE_ID,AMOUNT FROM FILES\$REFERENCES WHERE REFERENCE = '{$invoice->INVOICE_NUMBER}' AND AMOUNT = $invoice->INVOICE_AMOUNT ");
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
                    );
                    $referencesObj->create($data);
                }
            }
        }
    }

    protected function closeInvoices()
    {
        $referenceObj = new Application_Model_FilesReferences();
        $sql = "SELECT REFERENCE_ID FROM FILES\$REFERENCES WHERE REFERENCE NOT IN (SELECT INVOICE_NUMBER FROM IMPORT\$INVOICES)";
        $results = $this->db->get_results($sql);
        foreach ($results as $row) {
            $referenceObj->close($row->REFERENCE_ID);
        }
    }


    protected function linkClients()
    {
        $clientObj = new Application_Model_Clients();

        $results = $this->db->get_results("SELECT DEVISION_CODE FROM IMPORT\$INVOICES GROUP BY DEVISION_CODE");
        foreach ($results as $row) {
            $clientId = $clientObj->getClientIdByCode($row->DEVISION_CODE);
            if (!empty($clientId)) {
                $sql = "UPDATE IMPORT\$INVOICES SET CLIENT_ID = $clientId WHERE DEVISION_CODE = '{$row->DEVISION_CODE}' ";
                $this->db->query($sql);
            }
        }
        return true;
    }

    protected function linkDebtors()
    {
        $debtorsObj = new Application_Model_Debtors();

        $trainType = $this->functions->getUserSetting('BASE_TRAIN_TYPE');

        $results = $this->db->get_results("SELECT CLIENT_NUMBER FROM IMPORT\$INVOICES GROUP BY CLIENT_NUMBER");
        foreach ($results as $row) {
            $debtorId = $this->db->get_var("SELECT DEBTOR_ID FROM FILES\$FILES WHERE REFERENCE = '{$row->CLIENT_NUMBER}'");
            if (empty($debtorId)) {
                $dataRow = $this->db->get_row("SELECT FIRST 1 *  FROM IMPORT\$INVOICES WHERE CLIENT_NUMBER = '{$row->CLIENT_NUMBER}'");

                $countryObj = new Application_Model_Countries();
                $countryId = $countryObj->getCountryByCode($dataRow->CLIENT_COUNTRY);
                if (empty($countryId)) {
                    $countryId = 4;
                }

                $languagesObj = new Application_Model_Languages();

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


    protected function handleInvoiceLine($line, $fileName)
    {
        $columns = $this->_getColums();


        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*1", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*2", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*3", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*4", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*5", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*6", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*7", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*8", "", $line[$columns['CLIENT_ZIPCODE']]);
        $line[$columns['CLIENT_ZIPCODE']] = str_replace("*9", "", $line[$columns['CLIENT_ZIPCODE']]);

        $bestandsnaam = substr($fileName, strrpos($fileName, "/", 1));
        $data = array(
            'DEVISION_CODE' => $line[$columns['DEVISION_CODE']],
            'CLIENT_NUMBER' => $line[$columns['CLIENT_NUMBER']],
            'CLIENT_NAME' => $line[$columns['CLIENT_NAME']],
            'CLIENT_ADDRESS' => $line[$columns['CLIENT_ADDRESS']],
            'CLIENT_ZIPCODE' => $line[$columns['CLIENT_ZIPCODE']],
            'CLIENT_PLACE' => $line[$columns['CLIENT_PLACE']],
            'CLIENT_COUNTRY' => $line[$columns['CLIENT_COUNTRY']],
            'CLIENT_LANGUAGE' => $line[$columns['CLIENT_LANGUAGE']],
            'CLIENT_TEL' => $line[$columns['CLIENT_TEL']],
            'CLIENT_EMAIL' => $line[$columns['CLIENT_EMAIL']],
            'CLIENT_VAT' => $line[$columns['CLIENT_VAT']],
            'INVOICE_AMOUNT' => $this->functions->dbBedrag($line[$columns['INVOICE_AMOUNT']]),
            'INVOICE_NUMBER' => $line[$columns['INVOICE_NUMBER']],
            'INVOICE_DATE' => $this->functions->date_dbformat($line[$columns['INVOICE_DATE']]),
            'INVOICE_DUEDATE' => $this->functions->date_dbformat($line[$columns['INVOICE_DUEDATE']]),
            'INVOICE_TYPE' => $line[$columns['INVOICE_TYPE']],
            'CREATION_DATE' => date("Y-m-d"),
        );
        $this->addData("IMPORT\$INVOICES", $data);
        return true;
    }

} 