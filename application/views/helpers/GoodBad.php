<?php

class Zend_View_Helper_GoodBad extends Zend_View_Helper_Abstract {

    public function GoodBad($value) {
        if ($value) {
            $span = "";
        } else {
             $span = "<span class='ui-icon-red ui-icon-circle-check'></span>";
        }
        return $span;
    }

}