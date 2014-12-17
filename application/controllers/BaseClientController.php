<?php

require_once 'application/controllers/BaseController.php';

class BaseClientController extends BaseController {

    public $clientId;
    public $client;

    public function init() {

        parent::init();

        $this->_helper->_layout->setLayout('client-layout');
        $session = new Zend_Session_Namespace('CLIENT');

        if ($this->getParam("clientId") > 0) {
            $this->clientId = $this->getParam("clientId");

            $session->clientId = $this->getParam("clientId");
        }

        if (empty($this->clientId) && !empty($session->clientId)) {
            $this->clientId = $session->clientId;
        }

        $this->loadClient();
        $this->view->headerTitle = "{$this->client->NAME}";

        if ($this->clientId > 1 ) {
            $this->view->showAllMenu = true;
        }



    }


    protected function loadClient() {
        $this->client = $this->db->get_row("SELECT * FROM CLIENTS\$CLIENTS_ALL_INFO 
            WHERE CLIENT_ID = {$this->clientId}");
        $this->view->client = $this->client;
    }

}

