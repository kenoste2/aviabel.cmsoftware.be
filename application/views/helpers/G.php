<?php

class Zend_View_Helper_G extends Zend_View_Helper_Abstract {

    public function G($code,$var = false) {
        $functions = new Application_Model_CommonFunctions();
        $text = $functions->T($code);
        if ($var !== false) {
            $text = str_replace('xVARx', $var, $text);
        }
        return $text;
    }

}