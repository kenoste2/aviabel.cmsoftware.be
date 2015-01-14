<?php

class Application_Form_FormHelper {

    /**
     * @internal param $config
     * @return Zend_Validate_Float
     */
    public static function getFloatValidator()
    {
        global $config;
        $floatValidator = new Zend_Validate_Float(array('locale' => 'nl'));
        if ($config->importAmountFormat == 'US') {
            $floatValidator = new Zend_Validate_Float(array('locale' => 'en'));
            return $floatValidator;
        }
        return $floatValidator;
    }
}
