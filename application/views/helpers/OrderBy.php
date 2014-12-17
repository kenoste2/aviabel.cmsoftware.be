<?php

class Zend_View_Helper_OrderBy extends Zend_View_Helper_Abstract {

    public function OrderBy($fieldName,$location) {
        global $config;
        $string = "<a href='{$config->rootLocation}{$location}/orderby/{$fieldName}'><span class='ui-icon ui-icon-zoomin'></span></a>";
        return $string;
    }

}

