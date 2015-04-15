<?php

require_once 'application/controllers/BaseDebtorController.php';

class DebtorDetailController extends BaseDebtorController {

    public function viewAction() {
        $obj = new Application_Model_Debtors();
        $userObj = new Application_Model_Users();

        $generalForm = new Application_Form_AddDebtor();
        $data = array();
        if ($this->getRequest()->isPost()) {
            if (isset($_POST['invite_for_external_access'])) {
                $debtorExternalAccessObj = new Application_Model_DebtorExternalAccess();

                $filesObj = new Application_Model_Files();
                $files = $filesObj->getFilesByDebtorId($this->debtor->DEBTOR_ID);
                if(count($files) > 0) {
                    $debtorExternalAccessObj->sendExternalAccessInviteMail($this->debtor, $files[0]);
                }

                $this->view->debtorInvited = true;

                $data = $this->loadInfoIntoFormFromDb($obj);
            }
            else if ($generalForm->isValid($_POST)) {
                $update = $data = $generalForm->getValues();
                $data['DEBTOR_SCORE'] = $_POST['DEBTOR_SCORE'] ? $_POST['DEBTOR_SCORE'] : 0;
                $update['BIRTH_DAY'] = $this->functions->date_dbformat($data['BIRTH_DAY']);
                $update['CREDIT_LIMIT'] = $this->functions->dbBedrag($data['CREDIT_LIMIT']);

                $obj->update($update);
                $obj->changeDebtorScore($data['DEBTOR_SCORE'], $data['DEBTOR_ID'], $userObj->getLoggedInUser()->USER_ID);

                $this->view->generalFormSaved = true;
            } else {
                $this->view->generalFormError = true;
                $this->view->errors = $generalForm->getErrors();
            }
        } else {
            $data = $this->loadInfoIntoFormFromDb($obj);
        }


        $generalForm->populate($data);
        $this->view->openAmount = $obj->getOpenAmount($this->debtorId);
        $this->view->totalAmount = $obj->getTotalAmount($this->debtorId);

        $delayInfo = $obj->calculatePaymentDelayAndPaymentNrInvoices($this->debtorId);
        $this->view->paymentDelay = $delayInfo->PAYMENT_DELAY;
        $this->view->nrOfPayments = $delayInfo->NR_OF_PAYMENTS;
        $this->view->generalForm = $generalForm;

        $vatNr = $obj->getDebtorField($this->debtorId,'VATNR');
        if ($this->moduleAccess('binformation') && strlen($vatNr) >=9 ) {

            $this->view->vat = $vatNr;

            $client = new Application_Model_Binformation();

            $this->view->binfo_counter = $client->getCounter();


            $report = $client->getDataByVat($vatNr);
            $xml = simplexml_load_string($report['XML']);
            $this->view->ratios = array('turnover','profit_loss_brought_forward','equity','net_profit_loss','gross_result'
            ,'amort_deprec_prov','rkb09','rkb12','rkb14','rkb17','rkb18','rkb19','average_staff','rkb25','rkc03');
            $this->view->report = $xml;
        } else {
            $this->view->report = false;
        }

        $this->view->currentRating = $data['DEBTOR_SCORE'];
        $this->view->email = $data['E_MAIL'];

    }

    /**
     * @param $obj
     * @return mixed
     */
    public function loadInfoIntoFormFromDb($obj)
    {
        $data = $obj->getArrayData($this->debtorId);
        $data['BIRTH_DAY'] = $this->functions->dateFormat($data['BIRTH_DAY']);
        $data['CREDIT_LIMIT'] = $this->functions->amount($data['CREDIT_LIMIT']);
        return $data;
    }

}

