<?php

class Zend_View_Helper_T extends Zend_View_Helper_Abstract {

    public function T($code,$var = false) {
        $functions = new Application_Model_CommonFunctions();
        $text = $functions->T($code);
        if ($var !== false) {
            $text = str_replace('xVARx', $var, $text);
        }
        echo $text;
        return "";
    }

}