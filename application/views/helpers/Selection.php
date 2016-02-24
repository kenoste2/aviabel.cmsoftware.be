<?php

class Zend_View_Helper_Selection extends Zend_View_Helper_Abstract {

    public function Selection($value) {
        if ($value) {
            $span = "<li class='fa fa-check-circle fa-fw'></li>";
        } else {
             $span = "<li class='fa fa-times-circle fa-fw'></li>";
        }
        return $span;
    }

}