<?php

require_once 'application/controllers/BaseController.php';

class TrajectController extends BaseController {

    public function followupAction() {
        $this->checkAccessAndRedirect(array('traject/followup'));

        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_traject_followup")  ;


        $numberOfDays = 15;
        $trainModel = new Application_Model_Train();

        $followUp = $trainModel->getTrainFollowup();

        $selectedCollectorId = false;
        if ($this->auth->online_rights == 3 && !empty($this->auth->online_collector_id) && !$this->getParam('showall')) {
            $selectedCollectorId  = $this->auth->online_collector_id;
            $this->view->showCollectorSelector = true;
            $this->view->bread .= " - " . $this->auth->online_collector_name . " <a href=" . $this->config->rootLocation. "/traject/followup/showall/1><li class='fa fa-search-minus fa-fw'></li></a>" ;
        }
        $this->view->selectedCollector = $selectedCollectorId;


        $counters = $trainModel->getCountersForFollowup($followUp, $numberOfDays, $selectedCollectorId);

        $this->view->followUp = $followUp;
        $this->view->numberOfDays = $numberOfDays;
        $this->view->counters = $counters;
    }

    public function listAction()
    {
        $i = $this->getRequest()->getParam('i', 0);
        $serie = $this->getRequest()->getParam('serie', 1);
        $collector = $this->getRequest()->getParam('collector');
        $type = $this->getRequest()->getParam('type', '');
        $session = new Zend_Session_Namespace('FILES');

        $trainModel = new Application_Model_Train();
        $results = $trainModel->performTrainSql($trainModel->getTrainByType($type), $i, $collector);

        $session->fileList = array();
        foreach ($results as $index => $result) {
            $session->fileList[$index] = (array)$result;
        }

        $this->view->results = $results;
    }

}

