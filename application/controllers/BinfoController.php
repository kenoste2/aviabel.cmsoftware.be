<?php

require_once 'application/controllers/BaseController.php';

class BinfoController extends BaseController
{

    public function binfoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $binfo = new Application_Model_Binformation();
        $vat  = $this->getParam("vat");
        $data = $binfo->getPdfReport($vat);
        print $data;
        die();
    }
}

