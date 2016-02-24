<?php

class Zend_View_Helper_Welcome extends Zend_View_Helper_Abstract {

    public function Welcome() {

        $auth = new Application_Model_AuthAdapterDbTable();

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentityName();
        } else {
            $identity = "";
        }
        return $identity;
    }

}