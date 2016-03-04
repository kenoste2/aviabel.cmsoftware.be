<?php

class Application_Form_PasswordReset extends Zend_Form {

    public function init() {
        
        $this->setMethod('post');

        $this->addElement('password', 'password', array(
            'label' => 'New Password',
            'required' => false,
        ));

        $this->addElement('password', 'password2', array(
            'label' => 'Confirm Password',
            'required' => false,
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Confirm Password',
        ));

    }

}

