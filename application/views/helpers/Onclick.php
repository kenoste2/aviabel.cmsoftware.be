<?php

class Zend_View_Helper_Onclick extends Zend_View_Helper_Abstract {

    public function onclick($location) {
        return 'onClick="window.open(\''.$location.'\',\'_parent\')"';
    }

}