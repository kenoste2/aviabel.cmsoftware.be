<?php

require_once 'application/controllers/BaseController.php';

class IndexController extends BaseController {

    public function indexAction() {

        if(!$this->hasMenuAccess('index/index')) {
            $this->_redirect('files/search');
        }

        $this->view->bread = "Dashboard";


        $filesAllInfoModel = new Application_Model_FilesAllInfo();
        $filesReferencesModel = new Application_Model_FilesReferences();
        $filesActionsObj = new Application_Model_FilesActions();
        $filesPaymentsObj = new Application_Model_FilesPayments();
        $statisticsForClientModel = new Application_Model_StatisticsForClient();
        $importedMailsObj = new Application_Model_ImportedMails();
        $agendaStatesObj = new Application_Model_AgendaStates();
        $debtorsObj = new Application_Model_Debtors();

        $selectedCollectorId = false;
        if ($this->auth->online_rights == 3 && !empty($this->auth->online_collector_id) && !$this->getParam('showall')) {
            $selectedCollectorId  = $this->auth->online_collector_id;
            $this->view->showCollectorSelector = true;
            $this->view->bread .= " - " . $this->auth->online_collector_name . " <a href=" . $this->config->rootLocation. "/index/index/showall/1><li class='fa fa-search-minus fa-fw'></li></a>" ;
        }
        $this->view->selectedCollector = $selectedCollectorId;



        $this->view->totalNotDue = $filesReferencesModel->getTotalNotDue($selectedCollectorId);
        $this->view->totalPastDue = $filesReferencesModel->getTotalPastDue($selectedCollectorId);


        $realtimeSummary = $filesAllInfoModel->getRealtimeSummary($selectedCollectorId);
        $realtimeSummaryTotal = $filesAllInfoModel->getRealtimeSummaryTotal($selectedCollectorId);

        $this->view->realtimeSummary = $realtimeSummary;
        $this->view->realtimeSummaryTotal = $realtimeSummaryTotal;

        $tobePrinted = $filesActionsObj->getToBePrintedAllCount($selectedCollectorId);
        $this->view->toBePrinted = $tobePrinted;


        $payedToday = $filesPaymentsObj->getDayPayments(date("Y-m-d"), $selectedCollectorId);
        $this->view->payedToday = $payedToday;

        $importedMailsToday = $importedMailsObj->getTodayCount($selectedCollectorId);
        $this->view->emailsToday = $importedMailsToday;

        $agendaStates = $agendaStatesObj->getList($selectedCollectorId);
        $this->view->agenda = $agendaStates;

        $paymentDelay = $debtorsObj->getMeanPaymentDelay($selectedCollectorId);
        $this->view->paymentDelay = $paymentDelay;


        $aging = $statisticsForClientModel->getGeneralAging($selectedCollectorId);
        $this->view->aging = $aging;

        $paymentDelayAverageObj = new Application_Model_PaymentDelayAverage();
        $this->view->paymentForecast = $paymentDelayAverageObj->getPaymentForecast(null, $selectedCollectorId);

        $disputesObj = new Application_Model_Disputes();
        $this->view->disputesToday = $disputesObj->countForToday($selectedCollectorId);
    }

}

