<?php

class Zend_View_Helper_Date extends Zend_View_Helper_Abstract {

    public function date($amount) {
        $functions = new Application_Model_CommonFunctions();
        return $functions->dateformat($amount);
    }

}