<?php

require_once 'application/models/Base.php';

class Application_Model_Print extends Application_Model_Base
{

    public function getTemplateContent($fileId, $templateId, $startDate = null, $nrOfPayments = null, $actionDate = null)
    {
        $templateObj = new Application_Model_Templates();
        $templateContentObj = new Application_Model_TemplatesContent();
        $fileObj = new Application_Model_File();
        $debtorObj = new Application_Model_Debtors();
        $clientObj = new Application_Model_Clients();

        $fields = array();
        $ppFields = array();

        $lang = $this->getLang($fileId,$templateId);

        $clientId = $fileObj->getFileField($fileId, 'CLIENT_ID');
        $collectorId = $fileObj->getFileField($fileId, 'COLLECTOR_ID');

        $template = $templateObj->getTemplateContent($templateId, $lang);
        $text = $template['CONTENT'];
        $smsText = $template['SMS_CONTENT'];
        $fileFields = $templateContentObj->getFileContent($text, $fileId, $templateId);
        $clientFields = $templateContentObj->getClientContent($text, $clientId);
        $collectorFields = $templateContentObj->getCollectorContent($collectorId);
        $dateFields = $templateContentObj->getDatesContent($text, $actionDate);
        $prevActions = $templateContentObj->getPrevActionsDates($text, $fileId);
        $invoices = $templateContentObj->getInvoices($fileId,$lang);
        $invoicesDetailed = $templateContentObj->getInvoicesDetail($fileId,$lang);
        $fileDocuments = $templateContentObj->getDocuments($fileId);
        $rpv = $templateContentObj->getRPV($fileId);
        $police = $templateContentObj->getPoliceContent($text,$fileId);



        if (!empty($startDate) && !empty($nrOfPayments)) {
            $ppFields = $templateContentObj->getPaymentPlanContent($fileId, $startDate, $nrOfPayments);
        } else {
            $ppFields = array();
        }

        if (empty($actionDate)) {
            $actionDate = date("d/m/Y");
        }

        $debtorExternalAccess = new Application_Model_DebtorExternalAccess();
        $actionDateField = array(
            'ACTION_DATE' => $actionDate,
        );

        $debtorId = $fileObj->getFileField($fileId, 'DEBTOR_ID');
        if($debtorId && (preg_match("/xEXTERNAL_ACCESS_LINK/", $text) || preg_match("/xEXTERNAL_ACCESS_LINK/", $smsText))) {
            $debtorObj = new Application_Model_Debtors();
            $debtor = $debtorObj->getDebtor($debtorId);
            $actionDateField['EXTERNAL_ACCESS_LINK'] = $debtorExternalAccess->createExternalAccessLink($debtor);
        }

        $inlineFooter = array(
            'INLINEFOOTER' => $this->functions->getUserSetting("templateAddText",$lang),
        );

        $fields = array_merge($fileFields,$clientFields,$dateFields
            ,$ppFields,$actionDateField,$prevActions,$inlineFooter
            ,$invoices,$invoicesDetailed,$rpv,$fileDocuments,$police,$collectorFields);
        $newText = $this->replaceFields($fields, $text);
        $smsNewText = $this->replaceFields($fields, $smsText);

        $result['CONTENT'] = $newText;
        $result['SMS_CONTENT'] = $smsNewText;
        $result['ADAPTABLE'] = $template['ADAPTABLE'];
        return json_encode($result);
    }


    /**
     * @param $to
     * @return string
     */
    public function formatAddress($to)
    {
        $baseAddress =  "{$to['NAME']}\n{$to['ADDRESS']}\n{$to['ZIP_CODE']} {$to['CITY']}";
        if($to['COUNTRY'] && $to['COUNTRY'] != "BELGIUM") {
            return "{$baseAddress}\n{$to['COUNTRY']}";
        }
        return $baseAddress;
    }

    public function replaceFields($fields, $text)
    {
        if (!empty($fields) && !empty($text)) {
            foreach ($fields as $field => $value) {
                if (is_float($value * 1)) {
                    $value = $this->functions->amount($value);
                }
                if ($date = $this->functions->dateformat($value)) {
                    $value = $date;
                }
                $text = str_replace("x{$field}x", $value, $text);
            }
        }
        return $text;
    }

    public function getToContent($fileId, $templateId)
    {
        $fileObj = new Application_Model_File();
        $templatesObj = new Application_Model_Templates();
        $template = $templatesObj->getTemplate($templateId);

        $data = array();

        switch ($template->TEMPLATE_FOR) {
            case 'C':
                $clientObj = new Application_Model_Clients();
                $clientId = $fileObj->getFileField($fileId, 'CLIENT_ID');
                $clientData = $clientObj->getArrayData($clientId);
                $data = array(
                    'NAME' => $clientData['NAME'],
                    'ADDRESS' => $clientData['ADDRESS'],
                    'ZIP_CODE' => $clientData['ZIP_CODE'],
                    'GSM' => $clientData['GSM'],
                    'CITY' => $clientData['CITY'],
                    'E_MAIL' => $clientData['E_MAIL'],
                    'COUNTRY' => $clientData['COUNTRY_DESCRIPTION']
                );
                break;
            case 'P':
                $data = array(
                    'NAME' => '',
                    'ADDRESS' => '',
                    'ZIP_CODE' => '',
                    'CITY' => '',
                    'GSM' => '',
                    'E_MAIL' => '',
                    'COUNTRY' => ''
                );
                break;
            default :
                $debtorObj = new Application_Model_Debtors();
                $debtorId = $fileObj->getFileField($fileId, 'DEBTOR_ID');
                $debtorData = $debtorObj->getArrayData($debtorId);

                $useName = $fileObj->getFileField($fileId, 'AFNAME_NAAM');
                if (!empty($useName)) {
                    $debtorData['NAME'] = $useName;
                }
                $useAddress = $fileObj->getFileField($fileId, 'AFNAME_ADRES');
                if (strlen($useAddress) > 10 ) {
                    $debtorData['ADDRESS'] = $useAddress;
                    $debtorData['ZIP_CODE'] = '';
                    $debtorData['CITY'] = '';
                }
                $data = array(
                    'NAME' => $debtorData['NAME'],
                    'ADDRESS' => $debtorData['ADDRESS'],
                    'ZIP_CODE' => $debtorData['ZIP_CODE'],
                    'CITY' => $debtorData['CITY'],
                    'COUNTRY' => $debtorData['COUNTRY_DESCRIPTION'],
                    'GSM' => $debtorData['GSM'],
                    'E_MAIL' => $debtorData['E_MAIL'],
                );
                break;
        }

        return $data;
    }


    public function getLang($fileId,$templateId)
    {
        $fileObj = new Application_Model_File();
        $clientObj = new Application_Model_Clients();
        $debtorObj = new Application_Model_Debtors();
        $templateObj = new Application_Model_Templates();


        $debtorId = $fileObj->getFileField($fileId, 'DEBTOR_ID');
        $clientId = $fileObj->getFileField($fileId, 'CLIENT_ID');

        $template = $templateObj->getTemplate($templateId);

        switch ($template->TEMPLATE_FOR) {
            case 'C':
                $lang = $clientObj->getClientField($clientId, 'LANGUAGE_CODE');
                break;
            default :
                $lang = $debtorObj->getDebtorField($debtorId, 'LANGUAGE_CODE');
                break;
        }

        $lang = $this->functions->langToCode($lang);
        return $lang;
    }

    public function getSettings()
    {

        $colums = $this->functions->getUserSetting('LETTERS_SETTINGS','NL');
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


}
