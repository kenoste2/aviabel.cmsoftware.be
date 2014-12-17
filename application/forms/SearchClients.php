<?php

class Application_Form_SearchClients extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $this->addElement('text', 'CODE', array('label' => $functions->T('code_c'), 'size' => 20));
        $this->addElement('text', 'NAME', array('label' => $functions->T('name_c'), 'size' => 35));
        $this->addElement('text', 'VAT_NO', array('label' => $functions->T('vat_c'), 'size' => 20));
        $this->addElement('text', 'ADDRESS', array('label' => $functions->T('address_c'), 'size' => 35));

        $this->addElement('hidden', 'search_client', array('value' => 1));


        $this->addDisplayGroup(array(
            'CODE',
            'NAME',
                ), 'group1');

        $group1 = $this->getDisplayGroup('group1');
        $group1->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:left;'))
        ));
        $this->addDisplayGroup(array(
            'VAT_NO',
            'ADDRESS',
                ), 'group2');

        $group2 = $this->getDisplayGroup('group2');
        $group2->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:right;'))
        ));


        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('search_c'),
        ));
    }

}

