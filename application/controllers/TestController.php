<?php

require_once 'application/controllers/BaseController.php';

class TestController extends BaseController
{
    //NOTE: a test method to see whether the standardizePhoneNumber method behaves like it should.
    //      Can be safely removed. It would make a good unit test though.
    public function testStandardizePhoneNumberAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $filesActionsObj = new Application_Model_FilesActions();

        $rawInternational = '+3214567891';
        $standRawInternational =  $filesActionsObj->standardizePhoneNumber($rawInternational);
        echo "<br>{$rawInternational} => {$standRawInternational}";

        $international = '003214567892';
        $standInternational =  $filesActionsObj->standardizePhoneNumber($international);
        echo "<br>{$international} => {$standInternational}";

        $belgian = '014567893';
        $standBelgian =  $filesActionsObj->standardizePhoneNumber($belgian);
        echo "<br>{$belgian} => {$standBelgian}";
    }
}
