<?php

require_once 'application/models/Base.php';

class Application_Model_PdfDocument extends Application_Model_Base {

    public $pdf;
    private $interestAccess;

    public function __construct($interestAccess){
        $this->interestAccess = $interestAccess;
        parent::__construct();
    }

    public function _initPdf()
    {
        define('FPDF_FONTPATH', 'library/FPDF/font/');
        require_once("library/MultiCellTag/class.multicelltag.php");
        define('EURO', chr(128));

        $printObj = new Application_Model_Print();

        $this->pdf = new fpdf_multicelltag('P', 'mm', array(210, 304));
        $this->pdf->AliasNbPages();
        $this->pdf->Open();

        $letterSettings = $printObj->getSettings();
        $this->lettertype = $letterSettings['LETTERTYPE'];
        $this->size = $letterSettings['SIZE'];
        $this->sizeSmall = $letterSettings['SIZESMALL'];
        $this->extraSmall = $letterSettings['SIZEEXTRASMALL'];

        $this->pdf->SetTitle("CMS");
        $this->pdf->SetMargins($letterSettings['MARGIN_LEFT'], $letterSettings['MARGIN_TOP'], $letterSettings['MARGIN_RIGHT']);
        $this->pdf->SetTextColor(0, 0, 0);

        $this->pdf->SetStyle("b", $this->lettertype, "B", $this->size, "0,0,0");
        $this->pdf->SetStyle("B", $this->lettertype, "B", $this->size, "0,0,0");
        $this->pdf->SetFont($this->lettertype, '', $this->size);
    }

    public function _loadContentToPdf($fileActionId, $reloadContent = false)
    {
        global $config;
        $fileActionsObj = new Application_Model_FilesActions();
        $templatesObj = new Application_Model_Templates();
        $printObj = new Application_Model_Print();

        $letterSettings = $printObj->getSettings();

        $fileObj = new Application_Model_File();

        $fileId = $fileActionsObj->getActionField($fileActionId, 'FILE_ID');

        $collectorCode = $fileObj->getFileField($fileId,'COLLECTOR_CODE');

        $templateId = $fileActionsObj->getActionField($fileActionId, 'TEMPLATE_ID');
        $actionId = $fileActionsObj->getActionField($fileActionId, 'ACTION_ID');

        $lang = $printObj->getLang($fileId, $templateId);


        $this->pdf->AddPage();
        $this->pdf->SetTextColor(0, 0, 0);


        $logoUrl = '././public/images/' . $letterSettings['LOGOFILE'] . '_' . $lang . '.jpg';
        if (!file_exists($logoUrl)) {
            $logoUrl = '././public/images/' . $letterSettings['LOGOFILE'] . '.jpg';
            if (!file_exists($logoUrl)) {
                $logoUrl = "";
            }
        }
        if (!empty($logoUrl)) {
            $this->pdf->Image($logoUrl, $letterSettings['LOGO_X'], $letterSettings['LOGO_Y'], $letterSettings['LOGO_H']);
        }

        $imageUrl = '././public/images/' . $letterSettings['IMAGEFILE'] . '_' . $lang . '.jpg';
        if (!file_exists($imageUrl)) {
            $imageUrl = '././public/images/' . $letterSettings['IMAGEFILE'] . '.jpg';
            if (!file_exists($imageUrl)) {
                $imageUrl = "";
            }
        }
        if (!empty($imageUrl)) {
            $this->pdf->Image($imageUrl, $letterSettings['IMAGE_X'], $letterSettings['IMAGE_Y'], $letterSettings['IMAGE_H']);
        }

        $destination = $fileActionsObj->getDestination($fileActionId);
        if ($destination['VIA'] == "POST") {
            $this->pdf->SetXY($letterSettings['ADDRESS_X'], $letterSettings['ADDRESS_Y']);
            $this->pdf->MultiCellTag(90, $letterSettings['LINE_HEIGHT'], utf8_decode($destination['ADDRESS']), 0, 'L');
        }

        if ($destination['VIA'] == "EMAIL") {
            $this->pdf->SetXY($letterSettings['ADDRESS_X'], $letterSettings['ADDRESS_Y']);
            $date = $fileActionsObj->getActionField($fileActionId, 'ACTION_DATE');
            $string = "<b>To: " . $destination['EMAIL'] . " \nOn: " . $this->functions->dateformat($date) . "</b>";
            $this->pdf->MultiCellTag(90, $letterSettings['LINE_HEIGHT'], utf8_decode($string), 0, 'L');
        }

        $this->pdf->SetXY($letterSettings['MARGIN_LEFT'], $config->templateTextPosition);

        if (!empty($reloadContent)) {
            $templateContentJson = $printObj->getTemplateContent($fileId, $templateId);
            $templateContent = json_decode($templateContentJson);
            $templateContent = $templateContent->CONTENT;
        } else {
            $templateContent = $fileActionsObj->getTemplateContent($fileActionId);
        }

        $templateContent = str_replace("€", "EUR", $templateContent);
        if ($config->decodeInPdf == 'Y') {
            $templateContent = utf8_decode($templateContent);
        }

        $first = true;

        $templateId = $fileActionsObj->getActionField($fileActionId, 'TEMPLATE_ID');
        $templateModules = $templatesObj->getTemplateModules($templateId);


        if (stripos($templateContent, '<newpage>') !== false) {
            $pieces = explode('<newpage>', $templateContent);
            foreach ($pieces as $piece) {
                if (!$first) {
                    $this->pdf->AddPage();
                    $this->pdf->SetTextColor(0, 0, 0);
                    $this->pdf->SetXY($letterSettings['MARGIN_LEFT'], $config->templateTextPosition2nd);
                }
                $this->pdf->MultiCellTag(0, $letterSettings['LINE_HEIGHT'], $piece, 0, "J", 0, 0, 0, 0, 0);
                if ($first) {
                    if (in_array(12, $templateModules)) {
                        $this->setSign($collectorCode);
                    } else {
                        $this->setSign();
                    }

                    $this->setFooter($lang);
                    $first = false;
                }
            }
        } else {
            $this->pdf->MultiCellTag(0, $letterSettings['LINE_HEIGHT'], $templateContent, 0, "J", 0, 0, 0, 0, 0);

            if (in_array(12, $templateModules)) {
                $this->setSign($collectorCode);
            } else {
                $this->setSign();
            }

            $this->setFooter($lang);
        }




        if (in_array(5, $templateModules)) {
            $this->loadPaymentForm($fileActionId);
        }
        if (in_array(6, $templateModules)) {
            $this->loadInvoices($fileActionId);
        }

        //$this->pdf->setY(5);
        //$this->pdf->setX(15);
        //$this->pdf->Cell(0, 5, $this->pdf->PageNo() . "/{nb}", 0, 1);


        return true;
    }

    public function setFooter($lang = 'NL', $width = 175, $height = false)
    {
        global $config;

        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();

        if (empty($height)) {
            $height = $letterSettings['FOOTER_Y'];
        }


        $footer = $this->functions->getUserSetting("template_footer", $lang);
        if (!empty($footer)) {
            $this->pdf->SetXY($letterSettings['MARGIN_LEFT'],$height);
            $this->pdf->SetFont($this->lettertype, '', $this->extraSmall);
            $this->pdf->MultiCellTag($width, 3, utf8_decode($footer), $letterSettings['FOOTER_BORDER'], $letterSettings['FOOTER_ALIGN']);
            $this->pdf->SetFont($this->lettertype, '', $this->size);
        }

    }

    public function setletterRemarks($lang)
    {

        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();
        $this->pdf->SetX($letterSettings['MARGIN_LEFT']);

        $remark = $this->functions->getUserSetting("letter_remarks", $lang);
        $remark = "\n\n" . $remark;
        $this->pdf->setX(10);
        $this->pdf->MultiCell(170, 4, utf8_decode($remark), 0, 'L');
        $this->pdf->SetFont($this->lettertype, '', $this->size);
    }


    public function setSign($prefix = false)
    {
        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();
        if (!empty($prefix)) {
            $signUrl = '././public/images/' . $prefix ."_".$letterSettings['SIGNFILE'] . '.jpg';
        } else {
            $signUrl = '././public/images/' . $letterSettings['SIGNFILE'] . '.jpg';
        }

        if (file_exists($signUrl)) {
            $x = $this->pdf->getX();
            $y = $this->pdf->getY() - $letterSettings['SIGN_ABOVE_TEXT'];
            $this->pdf->Image($signUrl, $x, $y, $letterSettings['SIGN_HEIGHT']);
        }
    }

    public function loadPaymentForm($fileActionId)
    {


        $fileObj = new Application_Model_File();
        $filesActionsObj = new Application_Model_FilesActions();
        $destination = $filesActionsObj->getDestination($fileActionId);
        $fileId = $filesActionsObj->getActionField($fileActionId, 'FILE_ID');
        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();


        $amount = $fileObj->getFileField($fileId, 'SALDO');
        $incassoKost = $fileObj->getFileField($fileId, 'INCASSOKOST');

        if (!$incassoKost) $incassoKost = 0;

        $amount = $amount + $incassoKost;


        $bp = $filesActionsObj->getPaymentPlan($fileActionId);
        if (!empty($bp)) {
            $amount = $bp->MONTHLY_AMOUNT;
        }

        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);


        $this->pdf->Text(35, 288, $fileObj->getFileField($fileId, 'CLIENT_NAME'));
        $this->pdf->Text(35, 292, $fileObj->getFileField($fileId, 'CLIENT_ADDRESS'));
        $this->pdf->Text(35, 296, $fileObj->getFileField($fileId, 'CLIENT_ZIP_CODE') . " " . $fileObj->getFileField($fileId, 'CLIENT_CITY'));

        $accountsObj = new Application_Model_Accounts();
        $account = $accountsObj->getRecieveAccount();


        $this->pdf->Text(35, 269, $account->ACCOUNT_NR);

        $this->pdf->Text(35, 278, $account->BIC);

        $value = "+" . number_format($amount, 2, ',', '') . "+";
        $this->pdf->Text(170, 220, $value);

        $structuredStatement = $fileObj->getFileField($fileId, 'STRUCTURED_STATEMENT');

        $statement = substr($structuredStatement, 0, 3) . "/" . substr($structuredStatement, 3, 4) . "/" . substr($structuredStatement, 7, 5);
        $this->pdf->Text(35, 290, "+++" . $statement . "+++");

        $this->pdf->SetFont($this->lettertype, '', $this->size);
    }


    public function loadInvoices($fileActionId)
    {

        global $config;

        $fileObj = new Application_Model_File();
        $fileReferencesObj = new Application_Model_FilesReferences();
        $filesActionsObj = new Application_Model_FilesActions();
        $destination = $filesActionsObj->getDestination($fileActionId);
        $fileId = $filesActionsObj->getActionField($fileActionId, 'FILE_ID');
        $templateId = $filesActionsObj->getActionField($fileActionId, 'TEMPLATE_ID');
        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();
        $lang = $printObj->getLang($fileId, $templateId);


        $file = $fileObj->getFileViewData($fileId);

        $address = $file->CLIENT_NAME . ": {$file->REFERENCE}\n{$file->DEBTOR_NAME}\n{$file->DEBTOR_ADDRESS}\n{$file->DEBTOR_COUNTRY_CODE} - {$file->DEBTOR_ZIP_CODE}  {$file->DEBTOR_CITY}";

        $this->pdf->AddPage('L');
        $this->pdf->SetX(0);
        $this->pdf->SetY($letterSettings['MARGIN_LEFT']);
        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);
        $this->pdf->MultiCell(0, $letterSettings['LINE_HEIGHT'], utf8_decode($address), 0, 'L');
        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);


        $valutas = $fileReferencesObj->getFileReferenceValutas($fileId);


        if (!empty($valutas)) {
            foreach ($valutas as $valuta) {
                $invoices = $fileReferencesObj->getReferencesByFileId($fileId, false,'A', $valuta->VALUTA);
                $this->generateInvoices($invoices, $lang);
            }
        }

        $logoUrl = '././public/images/' . $letterSettings['LOGOFILE'] . '_' . $lang . '.jpg';
        if (!file_exists($logoUrl)) {
            $logoUrl = '././public/images/' . $letterSettings['LOGOFILE'] . '.jpg';
            if (!file_exists($logoUrl)) {
                $logoUrl = "";
            }
        }
        if (!empty($logoUrl)) {
            $this->pdf->Image($logoUrl, $letterSettings['LOGO_INVOICES_X'], $letterSettings['LOGO_INVOICES_Y'], $letterSettings['LOGO_H']);
        }


        $this->setletterRemarks($lang);
        $this->setFooter($lang,$letterSettings['FOOTER_INVOICES_WIDTH'],$letterSettings['FOOTER_INVOICES_Y'] );
    }


    private function generateInvoices($invoices, $lang) {

        global $config;

        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();


        if (!empty($invoices)) {
            $this->pdf->Ln();
            $this->pdf->SetFont($this->lettertype, 'B', $this->sizeSmall);
            $this->pdf->Cell(35, $letterSettings['LINE_HEIGHT'], $this->functions->T("factuurdatum_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
            $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], $this->functions->T("vervaldatum_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
            $this->pdf->Cell(45, $letterSettings['LINE_HEIGHT'], $this->functions->T("contract_number", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
            $this->pdf->Cell(60, $letterSettings['LINE_HEIGHT'], $this->functions->T("contract_insured", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
            $this->pdf->Cell(45, $letterSettings['LINE_HEIGHT'], $this->functions->T("reference_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
            $this->pdf->Cell(35, $letterSettings['LINE_HEIGHT'], $this->functions->T("amount_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
            if ($this->interestAccess) {
                $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->T("interest_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
                $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->T("costs_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
                $this->pdf->Cell(25, $letterSettings['LINE_HEIGHT'], $this->functions->T("total_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
            }
            $this->pdf->Ln($letterSettings['LINE_HEIGHT']);


            $this->pdf->SetFont($this->lettertype, 'B', $this->sizeSmall);
            $total = 0;
            foreach ($invoices as $invoice) {

                $valuta = $invoice->VALUTA;

                $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);
                if (empty($invoice->INVOICE_DATE)) {
                    $invoice->INVOICE_DATE = $invoice->START_DATE;
                }
                $this->pdf->Cell(35, $letterSettings['LINE_HEIGHT'], $this->functions->dateformat($invoice->INVOICE_DATE), 'LRB', 0, 'L');
                $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], $this->functions->dateformat($invoice->START_DATE), 'LRB', 0, 'L');
                $this->pdf->Cell(45, $letterSettings['LINE_HEIGHT'], substr($invoice->CONTRACT_NUMBER,0,25), 'LRB', 0, 'L');
                $this->pdf->Cell(60, $letterSettings['LINE_HEIGHT'], substr($invoice->CONTRACT_INSURED,0,37), 'LRB', 0, 'L');
                $this->pdf->Cell(45, $letterSettings['LINE_HEIGHT'], substr($invoice->REFERENCE,0,25), 'LRB', 0, 'L');
                $this->pdf->Cell(35, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->AMOUNT) . " {$valuta}", 'LRB', 0, 'R');
                if ($this->interestAccess) {
                    $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->INTEREST) . " {$valuta}", 'LRB', 0, 'R');
                    $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->COSTS) . " {$valuta}", 'LRB', 0, 'R');
                    $this->pdf->Cell(25, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->TOTAL) . " {$valuta}", 'LRB', 0, 'R');
                    $total += $invoice->TOTAL;
                } else {
                    $total += $invoice->AMOUNT;
                }
                $this->pdf->Ln();
            }
            $this->pdf->SetFont($this->lettertype, 'B', $this->sizeSmall);
            if ($this->interestAccess) {
                $this->pdf->Cell(280, $letterSettings['LINE_HEIGHT'], $this->functions->T("total_c", $lang, $config->decodeInPdf), 'T', 0, 'R');
                $this->pdf->Cell(25, $letterSettings['LINE_HEIGHT'], $this->functions->amount($total) . " {$valuta}", 'LRB', 0, 'R');
            } else {
                $this->pdf->Cell(213, $letterSettings['LINE_HEIGHT'], $this->functions->T("total_c", $lang, $config->decodeInPdf), 'T', 0, 'R');
                $this->pdf->Cell(35, $letterSettings['LINE_HEIGHT'], $this->functions->amount($total) . " {$valuta}", 'LRB', 0, 'R');
            }
            $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);

        }
    }

}
