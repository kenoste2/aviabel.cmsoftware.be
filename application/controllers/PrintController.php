<?php

require_once 'application/controllers/BaseController.php';

class PrintController extends BaseController
{
    public $lettertype = "Arial";
    public $size = 10;
    public $sizeSmall = 9;
    public $extraSmall = 7;
    public $letterSettings = array();

    public $pdf;


    protected function _initPdf()
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
        $this->pdf->SetFont($this->lettertype, '', '10');
    }

    protected function _loadContentToPdf($fileActionId)
    {
        global $config;
        $fileActionsObj = new Application_Model_FilesActions();
        $templatesObj = new Application_Model_Templates();
        $printObj = new Application_Model_Print();
        $filesSObj = new Application_Model_File();

        $letterSettings = $printObj->getSettings();

        $fileId = $fileActionsObj->getActionField($fileActionId,'FILE_ID');
        $templateId = $fileActionsObj->getActionField($fileActionId,'TEMPLATE_ID');
        $clientId = $filesSObj->getClientId($fileId);

        $lang = $printObj->getLang($fileId,$templateId);


        $this->pdf->AddPage();
        $this->pdf->SetTextColor(0, 0, 0);


        $logoUrl = '././public/images/'.$letterSettings['LOGOFILE'].'_' . $clientId . '.jpg';
        if (!file_exists($logoUrl)) {
            $logoUrl = '././public/images/'.$letterSettings['LOGOFILE'].'_' . $lang . '.jpg';
        }
        if (!file_exists($logoUrl)) {
            $logoUrl = '././public/images/'.$letterSettings['LOGOFILE'] . '.jpg';
            if (!file_exists($logoUrl)) {
                $logoUrl = "";
            }
        }


        if (!empty($logoUrl)) {
            $this->pdf->Image($logoUrl, $letterSettings['LOGO_X'], $letterSettings['LOGO_Y'], $letterSettings['LOGO_H']);
        }

        $imageUrl = '././public/images/'.$letterSettings['IMAGEFILE'].'_' . $lang . '.jpg';
        if (!file_exists($imageUrl)) {
            $imageUrl = '././public/images/'.$letterSettings['IMAGEFILE'] . '.jpg';
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
        $templateContent = $fileActionsObj->getTemplateContent($fileActionId);
        $templateContent = str_replace("€", "EUR", $templateContent);
        if ($config->decodeInPdf == 'Y') {
            $templateContent = utf8_decode($templateContent);
        }

        $first = true;

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
                    $this->setSign();
                    $this->setFooter($lang, $clientId);
                    $first = false;
                }
            }
        } else {
            $this->pdf->MultiCellTag(0, $letterSettings['LINE_HEIGHT'], $templateContent, 0, "J", 0, 0, 0, 0, 0);
            $this->setSign();
            $this->setFooter($lang, $clientId);
        }

        $templateId = $fileActionsObj->getActionField($fileActionId, 'TEMPLATE_ID');
        $templateModules = $templatesObj->getTemplateModules($templateId);

        if (in_array('PaymentForm',$templateModules)) {
            $this->loadPaymentForm($fileActionId);
        }
        if (in_array('Invoices',$templateModules)) {
            $this->loadInvoices($fileActionId);
        }


    }

    public function templateAction()
    {
        global $config;

        $this->_initPdf();

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $fileActionId = $this->getParam('fileActionId');
        $this->_loadContentToPdf($fileActionId);

        $fileName = $config->rootFileActionDocuments . "/{$fileActionId}.pdf";


        if (!file_exists($fileName)) {
            $this->pdf->Output($fileName);
            $this->pdf->Output();
        } else {
            $this->getResponse()
                ->setHeader('Content-Disposition', 'inline; filename='.$fileActionId.'.pdf')
                ->setHeader('Content-type', 'application/x-pdf');
            print file_get_contents($fileName);
        }


    }

    public function toBePrintedAction()
    {
        global $config;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $fileActionsObj = new Application_Model_FilesActions();
        $templateId = $this->getParam('templateId');

        $toBePrinted = $fileActionsObj->getToBePrinted($templateId);

        if (!empty($toBePrinted)) {
            $this->_initPdf();
            foreach ($toBePrinted as $action) {
                $this->_loadContentToPdf($action->FILE_ACTION_ID);
                /*
                 *  TODO This does not work
                 $url = "{$config->rootLocation}/print/template/fileActionId/{$action->FILE_ACTION_ID}";
                print "<br>$url";
                file_get_contents($url);
                */
            }

            $this->pdf->Output();
        }
    }


    public function documentsAction()
    {
        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_print_documents");


        $filesActionsObj = new Application_Model_FilesActions();

        $templateId = $this->getParam('templateId');
        if ($this->getParam("setPrinted") && $templateId) {
            $filesActionsObj->setPrinted($templateId);
        }

        $tobePrinted = $filesActionsObj->getToBePrintedCount();
        $this->view->toBePrinted = $tobePrinted;

    }


    public function setFooter($lang = 'NL', $clientId = false)
    {
        global $config;

        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();

        if (!empty($clientId)) {
            $clientObj = new Application_Model_Clients();
            $footer = $clientObj->getTemplateFooter($clientId);
        }

        if (empty($footer)) {
            $footer = $this->functions->getUserSetting("template_footer",$lang);
        }



        if (!empty($footer)) {
            $this->pdf->SetXY($letterSettings['MARGIN_LEFT'], $letterSettings['FOOTER_Y']);
            $this->pdf->SetFont($this->lettertype, '', $this->extraSmall);
            $this->pdf->MultiCellTag(175, 3, utf8_decode($footer), $letterSettings['FOOTER_BORDER'], $letterSettings['FOOTER_ALIGN']);
            $this->pdf->SetFont($this->lettertype, '', $this->size);
        }

    }


    public function setSign() {
        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();
        $signUrl = '././public/images/'.$letterSettings['SIGNFILE'] . '.jpg';
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
        $iban = $this->functions->getUserSetting('setting_iban_general','NL');
        $bic = $this->functions->getUserSetting('setting_bic_general','NL');
        $name = $this->functions->getUserSetting('setting_address_client','NL');
        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);


        $name = explode("\n", $name);
        $this->pdf->Text(35, 288, $name[0]);
        $this->pdf->Text(35, 292, $name[1]);
        $this->pdf->Text(35, 296, $name[2]);


        $this->pdf->Text(35, 269, $iban);

        $this->pdf->Text(35, 278, $bic);

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
        $templateId = $filesActionsObj->getActionField($fileActionId,'TEMPLATE_ID');
        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();
        $lang = $printObj->getLang($fileId,$templateId);


        $file = $fileObj->getFileViewData($fileId);

        $address = "{$file->DEBTOR_NAME}\n{$file->DEBTOR_ADDRESS}\n{$file->DEBTOR_COUNTRY_CODE} - {$file->DEBTOR_ZIP_CODE}  {$file->DEBTOR_CITY}";

        $this->pdf->AddPage();
        $this->pdf->SetX(0);
        $this->pdf->SetY($letterSettings['MARGIN_LEFT']);
        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);
        $this->pdf->MultiCell(0, $letterSettings['LINE_HEIGHT'], utf8_decode($address), 0, 'L');
        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);


        $templateObj = new Application_Model_Templates();

        $excludeDisputes = $templateObj->excludeDispute($templateId);


        $invoices = $fileReferencesObj->getReferencesByFileId($fileId, $excludeDisputes, 'N' );
        if (!empty($invoices)) {
            $this->pdf->Ln();
            $this->pdf->SetFont($this->lettertype, '', $this->size);
            $this->pdf->MultiCell(0, $letterSettings['LINE_HEIGHT'], $this->functions->T("not_due_list_c", $lang, $config->decodeInPdf), 0, 'L');
            $this->addInvoicesTable($invoices, $lang);
        }

        $invoices = $fileReferencesObj->getReferencesByFileId($fileId, $excludeDisputes, 'Y' );
        if (!empty($invoices)) {
            $this->pdf->Ln();
            $this->pdf->SetFont($this->lettertype, '', $this->size);
            $this->pdf->MultiCell(0, $letterSettings['LINE_HEIGHT'], $this->functions->T("due_list_c", $lang, $config->decodeInPdf), 0, 'L');
            $this->addInvoicesTable($invoices, $lang);
        }
        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);

        $this->setFooter($lang, $clientId = false);
    }


    public function addInvoicesTable ($invoices, $lang)
    {

        global $config;

        $printObj = new Application_Model_Print();
        $letterSettings = $printObj->getSettings();

        $this->pdf->Ln();
        $this->pdf->SetFont($this->lettertype, 'B', $this->sizeSmall);
        $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], $this->functions->T("factuurdatum_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
        $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], $this->functions->T("vervaldatum_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
        $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], $this->functions->T("reference_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'L');
        $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->T("amount_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
        if ($this->moduleAccess('intrestCosts')) {
            $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->T("interest_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
            $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->T("costs_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
            $this->pdf->Cell(25, $letterSettings['LINE_HEIGHT'], $this->functions->T("total_c", $lang, $config->decodeInPdf), 'TLRB', 0, 'R');
        }
        $this->pdf->Ln($letterSettings['LINE_HEIGHT']);


        $this->pdf->SetFont($this->lettertype, 'B', $this->sizeSmall);
        $total = 0;
        foreach ($invoices as $invoice) {
            $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);
            if (empty($invoice->INVOICE_DATE)) {
                $invoice->INVOICE_DATE = $invoice->START_DATE;
            }
            $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], $this->functions->dateformat($invoice->INVOICE_DATE), 'LRB', 0, 'L');
            $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], $this->functions->dateformat($invoice->START_DATE), 'LRB', 0, 'L');
            $this->pdf->Cell(28, $letterSettings['LINE_HEIGHT'], utf8_decode($invoice->REFERENCE), 'LRB', 0, 'L');
            $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->AMOUNT), 'LRB', 0, 'R');
            if ($this->moduleAccess('intrestCosts')) {
                $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->INTEREST), 'LRB', 0, 'R');
                $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->COSTS), 'LRB', 0, 'R');
                $this->pdf->Cell(25, $letterSettings['LINE_HEIGHT'], $this->functions->amount($invoice->TOTAL), 'LRB', 0, 'R');
                $total += $invoice->TOTAL;
            } else {
                $total += $invoice->AMOUNT;
            }
            $this->pdf->Ln();
        }
        $this->pdf->SetFont($this->lettertype, 'B', $this->sizeSmall);
        if ($this->moduleAccess('intrestCosts')) {
            $this->pdf->Cell(147, $letterSettings['LINE_HEIGHT'], $this->functions->T("total_c", $lang, $config->decodeInPdf), 'T', 0, 'R');
            $this->pdf->Cell(25, $letterSettings['LINE_HEIGHT'], $this->functions->amount($total), 'LRB', 0, 'R');
        } else {
            $this->pdf->Cell(84, $letterSettings['LINE_HEIGHT'], $this->functions->T("total_c", $lang, $config->decodeInPdf), 'T', 0, 'R');
            $this->pdf->Cell(21, $letterSettings['LINE_HEIGHT'], $this->functions->amount($total), 'LRB', 0, 'R');
        }
        $this->pdf->SetFont($this->lettertype, '', $this->sizeSmall);

    }


    public function getTemplateContentAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $print = new Application_Model_Print();

        $templateId = $this->getParam('templateId');

        $fileId = $this->getParam('fileId');

        $session = new Zend_Session_Namespace('ADDACTION');

        $startDate = null;
        $nrOfPayments = null;
        if (!empty($session->STARTDATE) && !empty($session->NR_PAYMENTS)) {
            $startDate = $session->STARTDATE;
            $nrOfPayments = $session->NR_PAYMENTS;
        }
        if (!empty($session->ACTION_DATE)) {
            $actionDate = $session->ACTION_DATE;
        }
        print $print->getTemplateContent($fileId, $templateId, $startDate, $nrOfPayments, $actionDate);
    }


}

