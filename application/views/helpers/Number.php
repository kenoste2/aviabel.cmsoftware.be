<?php

class Zend_View_Helper_Number extends Zend_View_Helper_Abstract {

    public function Number($amount) {
        $functions = new Application_Model_CommonFunctions();
        return $functions->amount($amount);
    }

}