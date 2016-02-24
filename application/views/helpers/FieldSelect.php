<?php

class Zend_View_Helper_FieldSelect extends Zend_View_Helper_Abstract {

    public function FieldSelect($fieldName,$values) {
        $functions = new Application_Model_CommonFunctions();
        $element = new Zend_Form_Element_Select($fieldName, array(
            'MultiOptions' => $values,
        ));
        return $element;
    }

}