<?php

require_once 'application/controllers/BaseController.php';

class IndexController extends BaseController {

    public function indexAction() {

        if(!$this->hasMenuAccess('index/index')) {
            $this->_redirect('files/search');
        }

        $filesAllInfoModel = new Application_Model_FilesAllInfo();
        $filesReferencesModel = new Application_Model_FilesReferences();
        $filesActionsObj = new Application_Model_FilesActions();
        $filesPaymentsObj = new Application_Model_FilesPayments();
        $statisticsForClientModel = new Application_Model_StatisticsForClient();
        $importedMailsObj = new Application_Model_ImportedMails();
        $agendaStatesObj = new Application_Model_AgendaStates();
        $debtorsObj = new Application_Model_Debtors();

        $this->view->totalNotDue = $filesReferencesModel->getTotalNotDue();
        $this->view->totalPastDue = $filesReferencesModel->getTotalPastDue();


        $realtimeSummary = $filesAllInfoModel->getRealtimeSummary();
        $realtimeSummaryTotal = $filesAllInfoModel->getRealtimeSummaryTotal();

        $this->view->realtimeSummary = $realtimeSummary;
        $this->view->realtimeSummaryTotal = $realtimeSummaryTotal;

        $tobePrinted = $filesActionsObj->getToBePrintedAllCount();
        $this->view->toBePrinted = $tobePrinted;


        $payedToday = $filesPaymentsObj->getDayPayments();
        $this->view->payedToday = $payedToday;

        $importedMailsToday = $importedMailsObj->getTodayCount();
        $this->view->emailsToday = $importedMailsToday;


        $agendaStates = $agendaStatesObj->getList();
        $this->view->agenda = $agendaStates;

        $paymentDelay = $debtorsObj->getMeanPaymentDelay();
        $this->view->paymentDelay = $paymentDelay;


        $aging = $statisticsForClientModel->getGeneralAging();
        $this->view->aging = $aging;

        $paymentDelayAverageObj = new Application_Model_PaymentDelayAverage();
        $this->view->paymentForecast = $paymentDelayAverageObj->getPaymentForecast();

        $disputesObj = new Application_Model_Disputes();
        $this->view->disputesToday = $disputesObj->countForToday();
    }

}

