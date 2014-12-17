<?php

class Zend_View_Helper_Location extends Zend_View_Helper_Abstract {

    public function location() {
        global $config;
        return $config->rootLocation;
    }

}