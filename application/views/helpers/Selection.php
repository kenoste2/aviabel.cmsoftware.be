<?php

class Zend_View_Helper_Selection extends Zend_View_Helper_Abstract {

    public function Selection($value) {
        if ($value) {
            $span = "<span class='ui-icon ui-icon-circle-check'></span>";
        } else {
             $span = "<span class='ui-icon ui-icon-circle-close'></span>";
        }
        return $span;
    }

}