<?php

class Application_Model_Custom_NumberValidate extends Zend_Validate_Abstract
{
    const FLOAT = 'float';

    protected $_messageTemplates = array(
        self::FLOAT => "'%value%' is not a floating point value"
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        if (stripos($value,",") !== false && stripos($value,".") === false) {
            $value = str_replace(",", ".", $value);
        }
        if (!is_numeric($value)) {
            $this->_error(self::FLOAT);
            return false;
        }
        return true;
    }
}