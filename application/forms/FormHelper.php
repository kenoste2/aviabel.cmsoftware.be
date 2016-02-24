<?php

class Application_Form_FormHelper {

    /**
     * @internal param $config
     * @return Zend_Validate_Float
     */
    public static function getFloatValidator()
    {
        global $config;
        $floatValidator = new Application_Model_Custom_NumberValidate();
        return $floatValidator;
    }
}
