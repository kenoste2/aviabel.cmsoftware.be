<?php

class Zend_View_Helper_GoodBad extends Zend_View_Helper_Abstract {

    public function GoodBad($value) {
        if ($value) {
            $span = "";
        } else {
             $span = "<li class='fa fa-check-circle fa-fw'></li>";
        }
        return $span;
    }

}