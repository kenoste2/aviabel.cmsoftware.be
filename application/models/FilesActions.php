<?php

require_once 'application/models/Base.php';

class Application_Model_FilesActions extends Application_Model_Base
{

    public function addStartAction($fileId)
    {
        $action = $this->db->get_row("SELECT * FROM FILES\$ACTIONS WHERE CODE='NEW'", ARRAY_A);
        if (!empty($action)) {
            $actionId = $this->addAction($fileId, $action);
        }
    }

    public function addAction($fileId, $action, $cost = true, $confirm = false)
    {

        $filesObj = new Application_Model_Files();
        $fileObj = new Application_Model_File();

        $orderCycleBefore = $this->getOrderCycle($fileId);

        if ($confirm === false) {
            $confirmActionsObj = new Application_Model_ConfirmActions();
            $confirmationNeeded = $confirmActionsObj->confirmationNeeded($action['ACTION_ID']);
            if ($confirmationNeeded === true) {
                $confirmed = 0;
            } else {
                $confirmed = 1;
            }
        } else {
            $confirmed = 1;
        }


        $data = array(
            'FILE_ID' => $fileId,
            'ACTION_ID' => $action['ACTION_ID'],
            'REMARKS' => $action['REMARKS'],
            'CONFIRMED' => $confirmed,
            'ACTION_USER' => $this->online_user,
        );


        if($action['TEMPLATE_ID']) {

            if ($action['VIA'] == 'EMAIL') {
                $data['EMAIL'] = $action['E_MAIL'];
            }
            if ($action['VIA'] == 'POST') {
                $data['ADDRESS'] = $action['ADDRESS'];
            }
            if ($action['VIA'] == 'SMS') {
                $data['GSM'] = $action['GSM'];
            }

            $data['VIA'] = $action['VIA'];
        }

        if ($action['PRINTED'] == 1) {
            $data['PRINTED'] = 'Y';
        } else {
            $data['PRINTED'] = 'N';
        }

        if (!empty($action['ACTION_DATE'])) {
            $data['ACTION_DATE'] = $action['ACTION_DATE'];
        } else {
            $data['ACTION_DATE'] = date("Y-m-d");
        }
        if (!empty($action['TEMPLATE_ID'])) {
            $data['TEMPLATE_ID'] = $action['TEMPLATE_ID'];
        } else {
            $data['TEMPLATE_ID'] = 0;
        }
        if (!empty($action['CONTENT'])) {
            $data['TEMPLATE_CONTENT'] = $action['CONTENT'];
        }

        if (empty($data['FILE_ID'])) {
            $data['FILE_ID'] = $fileId;
        }
        if ($confirmed == 1) {

            $actionId = $this->addData('FILES$FILE_ACTIONS', $data, 'FILE_ACTION_ID');

            if ($cost === true) {
                $costId = $this->getActionCost($action['ACTION_ID']);
                if (!empty($costId)) {
                    $costObj = new Application_Model_FilesCosts();
                    $costData = array(
                        'FILE_ID' => $data['FILE_ID'],
                        'AMOUNT' => $fileObj->getActionCost($data['FILE_ID'], $costId),
                        'FILE_ACTION_ID' => $actionId,
                        'INVOICEABLE' => 1,
                        'COST_ID' => $costId,
                    );
                    $costObj->add($costData);
                }
            }

            if (!empty($action['FILE_STATE_ID'])) {
                $filesObj->setState($fileId, $action['FILE_STATE_ID']);
            }
            $stateId = $this->getState($action['ACTION_ID']);
            if ($stateId) {
                $filesObj->setState($fileId, $stateId);
            }

            $orderCycleAfter = $this->getOrderCycle($fileId);

            if ($orderCycleAfter >= $orderCycleBefore) {
                $this->setInvoicesOrderCycle($fileId, $orderCycleAfter);
            }

            $this->deleteAgendaForFile($fileId);

            return $actionId;

        } else {
            $data['CREATION_DATE'] = date("Y-m-d");
            $this->deleteAgendaForFile($fileId);
            $agendaId = $this->addData('FILES$FILE_AGENDA', $data, 'FILE_AGENDA_ID');
            return "NEED_CONFIRMATION";
        }

    }


    public function deleteAgendaForFile($fileId) {

        $sql = "DELETE FROM FILES\$FILE_AGENDA WHERE FILE_ID = {$fileId}";
        $this->db->query($sql);
    }


    public function getOrderCycle($fileId)
    {
        $fileObj = new Application_Model_File();
        $trainObj = new Application_Model_Train();

        $debtorId = $fileObj->getFileField($fileId, 'DEBTOR_ID');

        $trainType = $this->db->get_var("SELECT TRAIN_TYPE FROM FILES\$DEBTORS WHERE DEBTOR_ID = '{$debtorId}'");
        $stateId = $fileObj->getFileField($fileId, 'STATE_ID');

        $orderCycle = $trainObj->getOrderCycleByState($stateId, $trainType);
        return $orderCycle;
    }


    public function setInvoicesOrderCycle($fileId, $maxOrderCycle)
    {
        $filesReferencesObj = new Application_Model_FilesReferences();
        $openInvoices = $filesReferencesObj->getReferencesByFileId($fileId, true, 'Y');


        if ($openInvoices) {
            foreach ($openInvoices as $invoice) {
                $filesReferencesObj->setNextOrderCycle($invoice->REFERENCE_ID, $maxOrderCycle);
            }
        }

    }

    public function getActionByCode($code)
    {
        return $this->db->get_var("SELECT ACTION_ID FROM FILES\$ACTIONS WHERE CODE = '{$code}'");
    }

    public function getActionCost($actionId)
    {
        return $this->db->get_var("SELECT COST_ID FROM FILES\$ACTIONS WHERE ACTION_ID = '{$actionId}'");
    }


    public function getState($actionId)
    {
        return $this->db->get_var("SELECT FILE_STATE_ID FROM FILES\$ACTIONS WHERE ACTION_ID = '{$actionId}'");
    }

    public function delete($id)
    {
        $this->db->query("DELETE FROM FILES\$FILE_ACTIONS WHERE FILE_ACTION_ID = {$id}");
        return true;
    }

    public function add($data, $allowPdfDocuments = false, $confirm = false)
    {
        global $config;

        if (!empty($config->convertUTF8)) {
            $data['CONTENT'] = utf8_encode($data['CONTENT']);
            $data['SMS_CONTENT'] = utf8_encode($data['SMS_CONTENT']);
        }
        $data['CONTENT'] = str_replace("'","`",$data['CONTENT']);


        $fileActionId = $this->addAction($data['FILE_ID'], $data, true ,$confirm);

        if (is_numeric($fileActionId) && $fileActionId != 'NEED_CONFIRMATION') {
            $data['FILE_ACTION_ID'] = $fileActionId;

            if (!empty($data['BP_NR_PAYMENTS']) && !empty($data['BP_STARTDATE'])) {
                $this->addPaymentPlan($data['FILE_ID'], $data);
            }
            $this->handleSendLogic($data['TEMPLATE_ID'], $fileActionId, $data, $data['CONTENT'], $data['SMS_CONTENT'], $data['VIA'], $allowPdfDocuments);
            return $fileActionId;

        } else {
            return 'NEED_CONFIRMATION';
        }

    }

    /**
     * @param $interestCostsAccess
     * @param $fileActionId
     */
    public function createPdfForAction($interestCostsAccess, $fileActionId)
    {
        global $config;

        $pdfDoc = new Application_Model_PdfDocument($interestCostsAccess);
        $pdfDoc->_initPdf();
        $pdfDoc->_loadContentToPdf($fileActionId);
        $fileName = $config->rootFileActionDocuments . "/{$fileActionId}.pdf";
        if (!file_exists($fileName)) {
            $pdfDoc->pdf->Output($fileName);
        }
    }

    /**
     * @param $templateId
     * @param $fileActionId
     * @param $toContent
     * @param $content
     * @param $smsContent
     * @param $communicationType
     * @param $smsContent
     * @param $communicationType
     * @param $allowPdfGeneration
     */
    public function handleSendLogic($templateId, $fileActionId, $data, $content, $smsContent, $communicationType, $allowPdfGeneration)
    {
        if ($templateId > 0) {

            if ($communicationType === 'EMAIL') {
                $this->sendFileActionMail($templateId, $data["E_MAIL"], $content, $data['FILE_ID'], $fileActionId);
            }

            if ($communicationType === 'SMS') {
                $this->sendSmsFileActionMail($templateId, $data["GSM"], $smsContent);
            }

            if ($communicationType === 'POST' && $allowPdfGeneration) {

                $interestCostsAccess = $this->functions->moduleAccess('intrestCosts');
                $this->createPdfForAction($interestCostsAccess, $fileActionId);
            }
        }
    }

    public function addPaymentPlan($fileId, $arrayData)
    {
        $obj = new Application_Model_TemplatesContent();

        $paymentPlan = $obj->getPaymentPlanContent($fileId, $arrayData['STARTDATE'], $arrayData['BP_NR_PAYMENTS']);

        $data = array(
            'FILE_ID' => $fileId,
            'NR_PAYMENTS' => $arrayData['BP_NR_PAYMENTS'],
            'START_DATE' => $arrayData['BP_STARTDATE'],
            'MONTHLY_AMOUNT' => $paymentPlan['BPMAANDBEDRAG'],
            'FILE_ACTION_ID' => $arrayData['FILE_ACTION_ID'],
        );

        $id = $this->addData('FILES$BETAALPLAN', $data, 'BETAALPLAN_ID');
        return $id;

    }



    public function getPaymentPlan($fileActionId)
    {
        $betaalplan = $this->db->get_row("select * from FILES\$BETAALPLAN where FILE_ACTION_ID={$fileActionId}");
        return $betaalplan;
    }

    public function getTemplateContent($fileActionId)
    {
        $templateContent = $this->db->get_var("select TEMPLATE_CONTENT from FILES\$FILE_ACTIONS where FILE_ACTION_ID={$fileActionId}");
        return $templateContent;
    }

    public function getDestination($fileActionId)
    {
        $action = $this->db->get_row("SELECT EMAIL,ADDRESS FROM FILES\$FILE_ACTIONS
        WHERE FILE_ACTION_ID = {$fileActionId} ");

        if ($action->EMAIL) {
            $result = array (
                'VIA' => 'EMAIL',
                'EMAIL' => $action->EMAIL,
                'ADDRESS' => $action->ADDRESS,
            );
        } elseif ($action->ADDRESS) {
            $result = array (
              'VIA' => 'POST',
              'ADDRESS' => $action->ADDRESS,
            );
        } else {
            $result = array(
                'VIA' => 'NA',
            );
        }

        return $result;
    }


    public function getActionsByFileId($fileId)
    {
        $sql = "select A.PRINTED,B.CONFIRMED,A.FILE_ACTION_ID,A.ACTION_DATE,A.DUE_DATE,A.ACTION_ID,A.ACTION_CODE,B.VIA,A.ACTION_DESCRIPTION,A.TEMPLATE_ID,A.REMARKS,A.ACTION_USER,B.TEMPLATE_CONTENT,A.FILE_ID
                from FILES\$FILE_ACTIONS_ALL_INFO A
                JOIN FILES\$FILE_ACTIONS B ON A.FILE_ACTION_ID = B.FILE_ACTION_ID
                where A.FILE_ID='$fileId' order by A.ACTION_DATE DESC ,A.FILE_ACTION_ID DESC";
        $results = $this->db->get_results($sql);
        return $results;
    }

    /**
     * @param $templateId
     * @param $emailAddress
     * @param $content
     * @return bool|\Email
     */
    public function sendFileActionMail($templateId, $emailAddress, $content , $fileId = false, $fileActionId = false) {

        global $config;

        $mail = new Application_Model_Mail();
        $template = new Application_Model_Templates();
        $subject = $template->getTemplateDescription($templateId);

        if (empty($subject)) {
            $subject = "";
        }

        $fileObj = new Application_Model_File();
        $reference = $fileObj->getFileField($fileId, 'REFERENCE');
        $clientCode = $fileObj->getFileField($fileId, 'CLIENT_CODE');
        $subject .= " #{$clientCode}-{$reference}#";

        $clientId = $fileObj->getClientId($fileId);
        $clientObj = new Application_Model_Clients();
        $client = $clientObj->getClientViewData($clientId);


        if ($config->sendMailsAsClient == 'Y') {
            $from = array (
                'name' => $client->NAME,
                'email' => $client->E_MAIL,
            );
        } else {
            $from = false;
        }

        $filesDocumentsObj = new Application_Model_FilesDocuments();
        $filesDocuments = $filesDocumentsObj->getDocumentsByFileActionId($fileActionId);


        $attachments = array();
        if (count($filesDocuments) > 0) {
            foreach ($filesDocuments as $filesDocument) {
                $filename = "{$config->rootFileDocuments}/{$filesDocument->FILENAME}";
                $attachments[] = array('content' => file_get_contents($filename), 'filename' => basename($filesDocument->DESCRIPTION));
            }
        }
        return $mail->sendMail($emailAddress,$subject,$content,false,$attachments, $from);
    }

    public function sendSmsFileActionMail($templateId, $phoneNumber, $smsContent) {
        $content = "{$smsContent}<END>";
        $strippedPhoneNumber = $this->standardizePhoneNumber($phoneNumber);
        $emailAddress = "{$strippedPhoneNumber}@smsemail.be";
        $template = new Application_Model_Templates();
        $subject = $template->getTemplateDescription($templateId);
        $mail = new Application_Model_Mail();
        return $mail->sendMail($emailAddress, utf8_decode($subject), utf8_decode($content), false, true);
    }

    /**
     * @param $phoneNumber
     * @return mixed
     */
    public function standardizePhoneNumber($phoneNumber)
    {
        $isRawInternational = false;
        //NOTE: numbers starting with + are considered to need no correction, just removal of non-numeric characters
        if(preg_match('/^\+\d/', $phoneNumber)) {
            $isRawInternational = true;
        }
        $cleanedUpNumber = preg_replace('/\D/', '', $phoneNumber);
        if($isRawInternational) {
            return $cleanedUpNumber;
        }
        //NOTE: numbers starting with 00 are considered international numbers. The 00 is chopped off.
        $matches = array();
        if(preg_match('/^00(.*)$/', $cleanedUpNumber, $matches)) {
            return $matches[1];
        }
        //NOTE: numbers starting with 0 are considered Belgian numbers. 32 is added to them.
        $matches = array();
        if(preg_match('/^0(.*)$/', $cleanedUpNumber, $matches)) {
            return "32" . $matches[1];
        }
        //NOTE: if none of the previous matched, just dump the cleaned up number
        return $cleanedUpNumber;
    }

    public function getActionsWithDocumentsByFileId($fileId) {
        global $config;

        if(!$fileId) {
            $fileId = 0;
        }

        $escFileId = $this->db->escape($fileId);

        $sql = "select A.FILE_ACTION_ID,A.ACTION_DATE,A.DUE_DATE,A.ACTION_ID,A.ACTION_CODE,A.ACTION_DESCRIPTION,A.TEMPLATE_ID,A.REMARKS,A.ACTION_USER,B.TEMPLATE_CONTENT
                from FILES\$FILE_ACTIONS_ALL_INFO A
                JOIN FILES\$FILE_ACTIONS B ON A.FILE_ACTION_ID = B.FILE_ACTION_ID
                where A.FILE_ID= {$escFileId} order by A.ACTION_DATE DESC ,A.FILE_ACTION_ID DESC";
        $actions =  $this->db->get_results($sql);
        $filteredActions = array();
        foreach($actions as $action) {
            if(file_exists($config->rootFileActionDocuments . "/{$action->FILE_ACTION_ID}.pdf")) {
                $filteredActions []= $action;
            }
        }
        return $filteredActions;
    }

    public function getToBePrintedCount($collectorId = false)
    {

        $queryExtra =  "";
        if (!empty($collectorId)) {
            $queryExtra .= "AND F.COLLECTOR_ID = {$collectorId}";
        }

        $sql = "SELECT A.TEMPLATE_ID,B.DESCRIPTION,F.COLLECTOR_NAME,F.COLLECTOR_ID,COUNT(*) AS COUNTER FROM FILES\$FILE_ACTIONS A
                  JOIN SYSTEM\$TEMPLATES B ON A.TEMPLATE_ID = B.TEMPLATE_ID
                  JOIN FILES\$FILES_ALL_INFO F ON A.FILE_ID = F.FILE_ID
                WHERE A.TEMPLATE_ID > 0 AND A.TEMPLATE_CONTENT !='' AND A.PRINTED != 'Y' AND A.ADDRESS != ''
                      AND B.VISIBLE = 'Y' AND A.ACTION_DATE <= CURRENT_DATE AND F.DATE_CLOSED is null AND F.STATE_ID != 40
                      {$queryExtra}
                  GROUP BY F.COLLECTOR_NAME,F.COLLECTOR_ID,A.TEMPLATE_ID, B.DESCRIPTION
                  ORDER BY F.COLLECTOR_NAME ASC, B.DESCRIPTION";
        $results = $this->db->get_results($sql);
        return $results;
    }

    public function getToBePrintedAllCount($collectorId = false)
    {
        $extraQuery = "";
        if (!empty($collectorId)) {
            $extraQuery .= " AND  F.COLLECTOR_ID = {$collectorId}";
        }


        $sql = "SELECT COUNT(*) AS COUNTER FROM FILES\$FILE_ACTIONS A
                  JOIN SYSTEM\$TEMPLATES B ON A.TEMPLATE_ID = B.TEMPLATE_ID
                  JOIN FILES\$FILES F ON F.FILE_ID = A.FILE_ID
                WHERE A.TEMPLATE_ID > 0 AND A.TEMPLATE_CONTENT !='' AND A.PRINTED != 'Y' AND A.ADDRESS != ''
                AND F.DATE_CLOSED is null AND F.STATE_ID != 40
                      AND B.VISIBLE = 'Y' $extraQuery";
        $count = $this->db->get_var($sql);
        return $count;

    }
    public function getToBePrinted($templateId , $collectorId)
    {
        $sql = "SELECT FIRST 500 FILE_ACTION_ID,ACTION_DATE FROM FILES\$FILE_ACTIONS A
                  JOIN SYSTEM\$TEMPLATES B ON A.TEMPLATE_ID = B.TEMPLATE_ID
                  JOIN FILES\$FILES_ALL_INFO F ON A.FILE_ID = F.FILE_ID
                WHERE A.TEMPLATE_ID = {$templateId} AND A.TEMPLATE_CONTENT !='' AND A.PRINTED != 'Y' AND A.ADDRESS != ''
                      AND B.VISIBLE = 'Y' AND ACTION_DATE<=CURRENT_DATE AND F.COLLECTOR_ID = {$collectorId}
                      AND F.DATE_CLOSED is null AND F.STATE_ID != 40 ORDER BY FILE_ACTION_ID";
        $results = $this->db->get_results($sql);
        return $results;
    }





    public function setPrinted($templateId, $collectorId)
    {
        $results = $this->getToBePrinted($templateId, $collectorId);
        if (!empty($results)) {
            foreach ($results as $row) {
                $this->_setActionAsPrinted($row->FILE_ACTION_ID);
            }
        }

        return $results;
    }

    public function getRemarkActions($fromDate = false, $toDate = false, $actionId = false, $userName = false )
    {
        $extraQuery = "";
        if (!empty($actionId)) {
            $extraQuery .= " AND A.ACTION_ID = {$actionId}";
        }

        if (!empty($fromDate)) {
            $extraQuery .= " AND A.ACTION_DATE  >=  '{$fromDate}'";
        }

        if (!empty($toDate)) {
            $extraQuery .= " AND A.ACTION_DATE  <=  '{$toDate}'";
        }

        if (!empty($userName)) {
            $extraQuery .= " AND A.ACTION_USER  =  '{$userName}'";
        }


        $sql = "SELECT A.*, F.FILE_ID, F.REFERENCE, F.DEBTOR_NAME FROM FILES\$FILE_ACTIONS_ALL_INFO A
                JOIN FILES\$FILES_ALL_INFO F ON F.FILE_ID = A.FILE_ID
                WHERE  A.REMARKS!=''
         {$extraQuery} ORDER BY A.ACTION_DATE DESC ";
        $results = $this->db->get_results($sql);
        return $results;
    }



    public function getActionField($fileActionId,$field) {
        $sql = "SELECT {$field} FROM FILES\$FILE_ACTIONS WHERE FILE_ACTION_ID  = $fileActionId";
        $row = $this->db->get_var($sql);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    public function getLastActions($fileId)
    {
        $results = $this->db->get_results("select FIRST 5 A.ACTION_DATE,B.CODE AS ACTION_CODE
      ,B.DESCRIPTION AS ACTION_DESCRIPTION,A.TEMPLATE_ID,A.FILE_ACTION_ID,A.ACTION_ID,A.TEMPLATE_CONTENT
      FROM FILES\$FILE_ACTIONS A
      JOIN FILES\$ACTIONS B ON A.ACTION_ID = B.ACTION_ID
  	    where FILE_ID='{$fileId}' order by FILE_ACTION_ID DESC");
        return $results;
    }


    public function getNextFileActionId(){
        $id  = $this->db->get_var("SELECT MAX(FILE_ACTION_ID) FROM FILES\$FILE_ACTIONS");
        $id++;
        return $id;
    }




    protected function _setActionAsPrinted($fileActionId) {
        $sql = "UPDATE FILES\$FILE_ACTIONS SET PRINTED='Y' WHERE FILE_ACTION_ID = {$fileActionId}";
        $this->db->query($sql);
        return true;
    }



}

?>
