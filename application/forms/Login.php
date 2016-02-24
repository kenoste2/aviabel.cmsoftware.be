<?php

class Application_Form_Login extends Zend_Form {

    public function init() {
        
        $this->setMethod('post');

        $this->addElement(
                'text', 'username', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('password', 'password', array(
            'label' => '',
            'required' => false,
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Login',
        ));

        $this->username->removeDecorator('label');
        $this->password->removeDecorator('label');
    }

}

