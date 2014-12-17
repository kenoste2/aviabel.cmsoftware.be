<?php

class Application_Form_ClientCosts extends Zend_Form {

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();

        $costsModel = new Application_Model_Filecosts();

        $this->addElement('text', 'FROM_DATE', array('label' => $functions->T('datum_c'), 'size'=> 30, 'required' => false));
        $this->addElement('text', 'UNTIL_DATE', array('label' => $functions->T('tot_c'), 'size'=> 30, 'required' => false));
        $this->addElement('select', 'COST_ID', array('label' => $functions->T('type_c') . " " . $functions->T('costs_c'), 'MultiOptions'=> $functions->db2array($costsModel->getFilecostsForSelect()), 'required' => false));

        $this->addDisplayGroup(array('FROM_DATE', 'COST_ID'), 'group1', array());
        $this->getDisplayGroup('group1')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:left;'))
        ));

        $this->addDisplayGroup(array('UNTIL_DATE'), 'group2', array());
        $this->getDisplayGroup('group2')->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'style' => 'width:50%;float:right;'))
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));

        $this->setDefaultValues();
    }

    protected function setDefaultValues()
    {
        $this->getElement('FROM_DATE')->setValue(date('01/m/Y'));
        $this->getElement('UNTIL_DATE')->setValue(date('d/m/Y'));
    }

}

