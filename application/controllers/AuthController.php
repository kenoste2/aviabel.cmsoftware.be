<?php

class AuthController extends Zend_Controller_Action {

    public function init() {
        global $config;

        $this->view->headerTitle = $config->appname;

        /* Initialize action controller here */
    }

    public function indexAction() {
        // action body
    }

    public function loginAction() {

        $authNamespace = new Zend_Session_Namespace('Zend_Auth');

        if (empty($authNamespace->loginAttempts)) {
            $authNamespace->loginAttempts = 0;
        }

        if (!empty($authNamespace->loginAttempts)) {
            if (time() - $authNamespace->lastAttempt > (15 * 60)) {
                $authNamespace->loginAttempts = 0;
            }
        }

        $this->_helper->_layout->setLayout('login-layout');

        $loginForm = new Application_Form_Login();

        if ($loginForm->isValid($_POST) && $loginForm->getValue('username')) {

            $adapter = new Application_Model_AuthAdapterDbTable();

            $adapter->setIdentity($loginForm->getValue('username'));
            $adapter->setCredential(sha1($loginForm->getValue('password')));

            $result = $adapter->authenticate();

            if (!$adapter->isValid()) {
                $adapter->setCredential(sha1($loginForm->getValue('password')));
                $result = $adapter->authenticate();
            }

            if ($adapter->isValid() && $authNamespace->loginAttempts <=3 ) {

                $authNamespace->loginAttempts = 0;

                $obj = new Application_Model_Base();
                $obj->log("{$loginForm->getValue('username')} logged in from {$_SERVER['REMOTE_ADDR']} at ".date("H:i"), "auth");
                $this->_redirect('/index/index');
                return;
            } else {
                $this->view->showError = true;
                if ($authNamespace->loginAttempts >=3) {
                    $this->view->attemptsError = true;
                }
                $authNamespace->loginAttempts++;
                $authNamespace->lastAttempt = time();
                $obj = new Application_Model_Base();
                $obj->log("login error for user {$loginForm->getValue('username')} from {$_SERVER['REMOTE_ADDR']} at ".date("H:i"), "auth");
            }
        }

        $this->view->loginForm = $loginForm;
    }

    public function logoutAction() {
        $auth = new Application_Model_AuthAdapterDbTable();
        $auth->clearIdentity();
        $this->_redirect('/Auth/Login');
    }

}

