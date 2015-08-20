<?php

class Zend_View_Helper_OrderBy extends Zend_View_Helper_Abstract {

    public function OrderBy($fieldName,$location) {
        global $config;
        $string = "<a href='{$config->rootLocation}{$location}/orderby/{$fieldName}'><li class='fa fa-search-plus fa-fw'></li></a>";
        return $string;
    }

}

