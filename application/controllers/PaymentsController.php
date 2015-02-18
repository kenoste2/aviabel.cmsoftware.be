<?php

require_once 'application/controllers/BaseController.php';

class PaymentsController extends BaseController {

    public function searchAction()
    {

        $this->view->bread = $this->functions->T("menu_financial") . "->" . $this->functions->T("menu_payments_search")  ;

        /*
         if ($this->auth->online_rights != 5) {
            $this->view->addButtonParent = "/payments/add/index/" . $this->getParam("index");
        }
        */

        $this->view->printButton = true;

        $searchForm = new Application_Form_SearchPayments();
        $paymentsAllInfoModel = new Application_Model_PaymentsAllInfo();

        if ($this->getRequest()->isPost()) {
            $searchForm->isValid($this->getRequest()->getPost());
        }

        $paymentsAllInfo = $paymentsAllInfoModel->searchPaymentsAllInfo(
            $searchForm->getValue('STARTDATE'),
            $searchForm->getValue('ENDDATE'),
            $searchForm->getValue('CLIENT'),
            $searchForm->getValue('FOR'),
            -1,
            $searchForm->getValue('ACCOUNT_ID')
        );
        $paymentsAllInfoTotal = $paymentsAllInfoModel->searchCountPaymentsAllInfo(
            $searchForm->getValue('STARTDATE'),
            $searchForm->getValue('ENDDATE'),
            $searchForm->getValue('CLIENT'),
            $searchForm->getValue('FOR'),
            -1,
            $searchForm->getValue('ACCOUNT_ID'));

        $this->view->searchForm = $searchForm;
        $this->view->paymentsAllInfo = is_null($paymentsAllInfo) ? array() : $paymentsAllInfo;
        $this->view->paymentsAllInfoTotal = $paymentsAllInfoTotal;
        $this->view->exportButton = count($paymentsAllInfo) ? true : false;
        $this->export->sql = count($paymentsAllInfo) ? $paymentsAllInfoModel->getSql() : '';
    }

    public function addAction()
    {
        $form = new Application_Form_Payments();
        $journalModel = new Application_Model_Journal();
//
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data = $add = $form->getValues();
                $journalModel->add($add);
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
        }
        $form->populate($data);

        $this->view->form = $form;
    }

    public function importAction()
    {
        $this->view->bread = $this->functions->T("menu_financial") . "->" . $this->functions->T("menu_payments_import")  ;

        $importForm = new Application_Form_Import();
        $tempPaymentModel = new Application_Model_TempPayments();

        if ($this->getParam("delete")) {
            $this->deleteImport($this->getParam("delete"));
            $this->view->deleted = true;
        }

        $rowCount = 0;
        if($this->getRequest()->isPost() && $importForm->isValid($this->getRequest()->getParams())){
            $import = new Application_Model_Import();
            $fileName = $import->processFile($_FILES['userfile'], '/../public/documents/imported_payments');
            $rowCount = $import->importPaymentCsv($fileName);
        }

        $tempPayments = $tempPaymentModel->getUntreatedTempPayments();

        $this->view->rowCount = $rowCount;
        $this->view->tempPayments = $tempPayments ? $tempPayments : array();
        $this->view->importForm = $importForm;
    }

    public function processImportedAction()
    {
        $tempPaymentModel = new Application_Model_TempPayments();
        $importModel = new Application_Model_Import();
        $fileModel = new Application_Model_Files();

        $fileNrs = $this->getRequest()->getParam('FILE_NR', array());


        $verwerkt = 0;
        foreach ($fileNrs as $paymentId => $fileNr) {
            if (!empty($fileNr)) {
                $tempPayment = $tempPaymentModel->getTempPayment($paymentId);
                $fileId = $fileModel->getFileIdByNumber($fileNr);

                if ($fileId) {
                    $result = $importModel->handlePaymentLine(
                        array(
                            $tempPayment->REFERENCE,
                            $tempPayment->INVOICE_REFERENCE,
                            $tempPayment->AMOUNT,
                            $tempPayment->PAYMENT_DATE,
                            (isset($tempPayment->ACCOUNT_CODE) ? $tempPayment->ACCOUNT_CODE : 'EXTERNAL'),
                            $tempPayment->CLIENT_CODE),
                        $tempPayment->FILENAME,
                        $fileId
                    );

                    if ($result) {
                        $tempPaymentModel->delete($paymentId);
                        $verwerkt++;
                    }
                }
            }
        }

        $this->_redirect('/payments/import/verwerkt/' . $verwerkt);
    }

    protected function deleteImport($id)
    {
        $tempPaymentModel = new Application_Model_TempPayments();
        $tempPaymentModel->delete($id);

        return true;
    }

}

