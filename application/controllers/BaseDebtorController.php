<?php

require_once 'application/controllers/BaseController.php';

class BaseDebtorController extends BaseController {

    public $debtorId;
    public $debtor;
    protected $index;

    public function init() {

        parent::init();

        $this->_helper->_layout->setLayout('debtor-layout');
        $session = new Zend_Session_Namespace('DEBTORS');

        if ($this->getParam("index") != "") {
            $indexes = $this->_getNextPrevCurrent();
            $this->debtorId = $indexes['currentDebtorId'];
            $this->view->indexes = $indexes;
            $this->view->index = $this->getParam("index");
        }

        if ($this->getParam("debtorId") > 0) {
            $this->debtorId = $this->getParam("debtorId");

            $session->debtorId = $this->getParam("debtorId");
        }

        if (empty($this->debtorId) && !empty($session->debtorId)) {
            $this->debtorId = $session->debtorId;
        }

        $this->loadDebtor();
        $this->view->headerTitle = "{$this->debtor->NAME}";
    }

    protected function _getNextPrevCurrent() {
        $session = new Zend_Session_Namespace('DEBTORS');
        $index = $this->getParam("index");

        $next = false;
        $prev = false;
        $currentDebtor = false;

        if (!empty($session->debtorList)) {
            switch ($index) {
                case 0:
                    $next = (key_exists(1, $session->debtorList)) ? 1 : false;
                    $prev = false;
                    break;
                case ($index > 0):
                    $next = (key_exists($index + 1, $session->debtorList)) ? $index + 1 : false;
                    $prev = (key_exists($index - 1, $session->debtorList)) ? $index - 1 : false;
                    break;
            }
            $currentDebtor = $session->debtorList[$index]['DEBTOR_ID'];
        }
        $indexes = array(
            'currentDebtorId' => $currentDebtor,
            'nextIndex' => $next,
            'prevIndex' => $prev,
        );

        return $indexes;
    }

    protected function loadDebtor() {
        $this->debtor = $this->db->get_row("SELECT * FROM FILES\$DEBTORS_ALL_INFO 
            WHERE DEBTOR_ID = {$this->debtorId}");
        $this->view->debtor = $this->debtor;
    }

}

