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

        if ($this->hasAccess('viewActionDocuments')) {
            $this->view->viewActionContent = true;
        }

        if ($this->moduleAccess('intrestCosts')) {
            $this->view->showIntrestCosts = true;
        }
        if (!$this->moduleAccess('partner')) {
            $generalForm->disablePartner();
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
                    $update['STATE_ID'] = $this->file->STATE_ID;
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
        // Populating form
        $debtorForm->populate($data);
        $this->view->debtorForm = $debtorForm;
        $this->view->fileForm = $generalForm;
        $debtorObj = new Application_Model_Debtors();
        $delayInfo = $debtorObj->calculatePaymentDelayAndPaymentNrInvoices($this->file->DEBTOR_ID);
        $this->view->paymentDelay = $delayInfo->PAYMENT_DELAY;
        $this->view->nrOfPayments = $delayInfo->NR_OF_PAYMENTS;
    }

}

