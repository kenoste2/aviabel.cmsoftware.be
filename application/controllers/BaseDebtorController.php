<?php

require_once 'application/controllers/BaseController.php';

class BaseDebtorController extends BaseController {

    public $debtorId;
    public $debtor;
    protected $index;

    public function init() {
        global $config;

        parent::init();

        $this->_helper->_layout->setLayout('debtor-layout');
        $session = new Zend_Session_Namespace('DEBTORS');

        if ($this->getParam("debtorId") > 0) {
            $this->debtorId = $this->getParam("debtorId");
            $session->debtorId = $this->getParam("debtorId");
        }

        if (empty($this->debtorId) && !empty($session->debtorId)) {
            $this->debtorId = $session->debtorId;
        }

        $this->loadDebtor();

        $this->view->debtorFileId = $this->db->get_var("SELECT FILE_ID FROM FILES\$FILES WHERE DEBTOR_ID = $this->debtorId ");
        $this->view->headerTitle = "{$this->debtor->NAME}";
        $this->view->baseHttp = $config->baseHttp;
    }

    protected function loadDebtor() {
        $this->debtor = $this->db->get_row("SELECT * FROM FILES\$DEBTORS_ALL_INFO 
            WHERE DEBTOR_ID = {$this->debtorId}");
        $this->view->debtor = $this->debtor;
    }

}

