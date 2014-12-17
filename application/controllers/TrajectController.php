<?php

require_once 'application/controllers/BaseController.php';

class TrajectController extends BaseController {

    public function followupAction() {
        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_traject_followup")  ;


        $numberOfDays = 15;
        $trainModel = new Application_Model_Train();

        $followUp = $trainModel->getTrainFollowup();
        $counters = $trainModel->getCountersForFollowup($followUp, $numberOfDays);

        $this->view->followUp = $followUp;
        $this->view->numberOfDays = $numberOfDays;
        $this->view->counters = $counters;
    }

    public function listAction()
    {
        $i = $this->getRequest()->getParam('i', 0);
        $serie = $this->getRequest()->getParam('serie', 1);
        $type = $this->getRequest()->getParam('type', '');
        $session = new Zend_Session_Namespace('FILES');

        $trainModel = new Application_Model_Train();
        $results = $trainModel->performTrainSql($trainModel->getTrainByType($type), $i);

        $session->fileList = array();
        foreach ($results as $index => $result) {
            $session->fileList[$index] = (array)$result;
        }

        $this->view->results = $results;
    }

}

