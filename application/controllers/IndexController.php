<?php

require_once 'application/controllers/BaseController.php';

class IndexController extends BaseController {

    public function indexAction() {

        $this->_redirect('/files/search');
    }

}

