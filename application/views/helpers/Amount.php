<?php

class Zend_View_Helper_Amount extends Zend_View_Helper_Abstract {

    public function amount($amount, $valuta = false) {
        $functions = new Application_Model_CommonFunctions();
        if (empty($valuta)) {
            return $functions->amount($amount);
        } else {
            return $functions->amount($amount). " {$valuta}";
        }
        return $functions->amount($amount);
    }

}