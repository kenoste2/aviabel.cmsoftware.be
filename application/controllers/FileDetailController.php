<?php

require_once 'application/controllers/BaseFileController.php';

class FileDetailController extends BaseFileController {

    public function viewAction() {

        //initialisation

        $generalForm = new Application_Form_FileGeneral(
            array(
            'isClient' => ($this->auth->online_rights == 5),
            'isCollector' => ($this->auth->online_rights == 6)
            ));

       $creditlimiteForm = new Application_Form_FileCreditLimite();



        if ($this->hasAccess('viewActionDocuments')) {
            $this->view->viewActionContent = true;
        }

        if ($this->moduleAccess('intrestCosts')) {
            $this->view->showIntrestCosts = true;
        }
        if (!$this->moduleAccess('partner')) {
            $generalForm->disablePartner();
        }

        if ($this->moduleAccess('valuta')) {
            $this->view->valuteModule = true;

            $refObj = new Application_Model_FilesReferences();
            $this->view->valutaAmounts = $refObj->getFileAmountsByValute($this->fileId);
        }




        $debtorForm = new Application_Form_FileGeneralDebtor();
        $fileActionsObj = new Application_Model_FilesActions();
        $fileObj = new Application_Model_File();

        $generalForm->checkFields();

        if ($this->hasAccess('changeFileState')) {
            $generalForm->STATE_ID->setAttrib('disabled', null);
        }
        if ($this->hasAccess('changeFileReference')) {
            $generalForm->REFERENCE->setAttrib('disabled', null);
        }
        if ($this->hasAccess('changeUseAddress')) {
            $this->view->showUseAddress = true;
        }

        $this->view->actions = $fileActionsObj->getLastActions($this->fileId);


        switch ($this->auth->online_rights) {
            case "5":
                $sql = "select REMARK_ID,REMARK_CLIENT AS REMARK,CREATION_DATE,CREATION_USER from FILES\$REMARKS where FILE_ID='{$this->fileId}' order by CREATION_DATE,REMARK_ID DESC";
                break;
            default :
                $sql = "select REMARK_ID,REMARK AS REMARK,CREATION_DATE,CREATION_USER from FILES\$REMARKS where FILE_ID='{$this->fileId}' order by CREATION_DATE,REMARK_ID DESC";
                break;
        }
        $this->view->remarks = $this->db->get_results($sql);

        $this->view->payments = $this->db->get_results("select CREATION_DATE,PAYMENT_DATE,SUM(AMOUNT) AS AMOUNT,ACCOUNT_CODE
	from FILES\$PAYMENTS_ALL_INFO where FILE_ID='{$this->fileId}' GROUP BY JOURNAL_ID,PAYMENT_DATE,CREATION_DATE,ACCOUNT_CODE order by PAYMENT_DATE DESC");


        // Proccessing generalForm
        $data = array();
        if ($this->getRequest()->isPost() && $this->getParam("GENERALFORM")) {
            if ($generalForm->isValid($_POST)) {
                $update = $data = $generalForm->getValues();
                unset($update['GENERALFORM']);

                if (empty($update['STATE_ID'])) {
                    $update['STATE_ID'] = $this->file2->STATE_ID;
                }

                $this->saveData('FILES$FILES', $update, "FILE_ID = {$this->fileId}");
                $this->loadFile();
                $this->view->generalFormSaved = true;
            } else {
                $this->view->generalFormError = true;
                $this->view->errors = $generalForm->getErrors();
            }
        } else {
            $data = array(
                'STATE_ID' => $this->file->STATE_ID,
                'REFERENCE' => $this->file->REFERENCE,
                'COLLECTOR_VISIBLE' => $this->file2->COLLECTOR_VISIBLE,
                'COLLECTOR_ID' => $this->file->COLLECTOR_ID,
                'VALUTA' => $this->file2->VALUTA,
            );
        }

        // Populating form
        if ($data['COLLECTOR_VISIBLE'] == "") {
            $data['COLLECTOR_VISIBLE'] = 0;
        }
        $data['CLIENT_NAME'] = $this->file->CLIENT_NAME;
        $data['CREATION_DATE'] = $this->functions->dateformat($this->file->CREATION_DATE);
        $next = $fileObj->getNextAction($this->fileId);
        if (!empty($next)) {
            $data['NEXT_ACTION'] = $this->functions->dateformat($next['date']) . " -> " . $next['actionCode'] . ", " . $next['actionDescription'];
        } else {
            $data['NEXT_ACTION'] = $this->functions->T("no_step_c");
        }
        $data['REFERENCE'] = $this->file->REFERENCE;
        $data['STATE_ID'] = $this->file->STATE_ID;
        $data['COLLECTOR_ID'] = $this->file->COLLECTOR_ID;

        $generalForm->populate($data);



        // Proccessing debtorForm
        $data = array();
        if ($this->getRequest()->isPost() && $this->getParam("DEBTORFORM")) {
            if ($debtorForm->isValid($_POST)) {
                $update = $data = $debtorForm->getValues();
                unset($update['DEBTORFORM']);
                $this->saveData('FILES$FILES', $update, "FILE_ID = {$this->fileId}");
                $this->view->debtorFormSaved = true;
            } else {
                $this->view->debtorFormError = true;
                $this->view->errors = $debtorForm->getErrors();
            }
        } else {
            $data = array(
                'AFNAME_NAAM' => $this->file->AFNAME_NAAM,
                'AFNAME_ADRES' => $this->file->AFNAME_ADRES,
            );
        }


        $debtorForm->populate($data);

        $data = array();
        if ($this->getRequest()->isPost() && $this->getParam("CREDITLIMITEFORM"))  {
            if ($creditlimiteForm->isValid($_POST)) {
                $update = $data = $creditlimiteForm->getValues();
                $update['OWN_CREDIT_LIMIT'] = $this->functions->dbBedrag($data['OWN_CREDIT_LIMIT']);
                unset($update['CREDITLIMITEFORM']);

                $this->saveData('FILES$FILES', $update, "FILE_ID = {$this->fileId}");
                $this->view->creditlimitSaved = true;

                $data['PROVIDER_CREDIT_LIMIT'] = $this->functions->amount($this->file2->PROVIDER_CREDIT_LIMIT);
                $data['INSURANCE_CREDIT_LIMIT'] = $this->functions->amount($this->file2->INSURANCE_CREDIT_LIMIT);

            } else {
                $this->view->creditlimitError = true;
                $this->view->errors = $creditlimiteForm->getErrors();
            }
        } else {
            $data = array(
                'OWN_CREDIT_LIMIT' => $this->functions->amount($this->file2->OWN_CREDIT_LIMIT),
                'PROVIDER_CREDIT_LIMIT' => $this->functions->amount($this->file2->PROVIDER_CREDIT_LIMIT),
                'INSURANCE_CREDIT_LIMIT' => $this->functions->amount($this->file2->INSURANCE_CREDIT_LIMIT),
            );
        }
        $creditlimiteForm->populate($data);

        $this->view->debtorForm = $debtorForm;
        $this->view->fileForm = $generalForm;
        $this->view->creditlimiteForm = $creditlimiteForm;
        $debtorObj = new Application_Model_Debtors();
        $delayInfo = $debtorObj->calculatePaymentDelayAndPaymentNrInvoices($this->file->DEBTOR_ID);
        $this->view->paymentDelay = $delayInfo->PAYMENT_DELAY;
        $this->view->nrOfPayments = $delayInfo->NR_OF_PAYMENTS;
    }

}

