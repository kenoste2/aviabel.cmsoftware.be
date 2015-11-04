<?php

class BaseController extends Zend_Controller_Action {

    protected $db;
    protected $auth;
    protected $export;
    protected $functions;
    protected $saveDate;

    public function init() {
        global $db;
        global $config;
        $this->db = $db;
        $auth = new Application_Model_AuthAdapterDbTable();
        $this->functions = new Application_Model_CommonFunctions();
        $exportNamespace = new Zend_Session_Namespace('export');
        $this->export = $exportNamespace;


        if ($this->getParam('selectlang')) {
            $lang = $this->getParam('selectlang');
            setcookie('lang', $lang , time() + 84600*30, '/');
            $this->_redirect('/Auth/logout');
        }

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();

        $this->nav = $controllerName."/".$actionName;
        $this->view->nav = $this->nav ;

        if (!$auth->hasIdentity() &&  $controllerName != 'cron' &&  $controllerName != 'download' ) {
            $this->redirect('/Auth/Login');
        }

        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        $this->auth = $authNamespace;

        $this->view->menu = $authNamespace->menu;

        $this->view->currentMenu = $this->getSubmenu();

        $this->view->headerTitle = $config->appname;
    }

    public function hasMenuAccess($key) {

        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        foreach($authNamespace->menu as $menu) {
            foreach($menu['SUBMENU'] as $submenu) {
                if($submenu['NAV'] === $key) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getSubmenu() {

        $authNamespace = new Zend_Session_Namespace('Zend_Auth');

        $menu = array();

        $controllerName = $this->getRequest()->getControllerName();

        if (!empty($authNamespace->menu)) {
            foreach ($authNamespace->menu as $menu) {
                foreach ($menu['SUBMENU'] as $submenu) {
                    list($subControllername) = explode("/",$submenu['NAV']);
                    if ($controllerName == $subControllername) {
                        return $menu;
                    }
                }
            }
        }
    }


    public function checkAccessAndRedirect($menuAccessItems, $accessItems = array()) {
        foreach($menuAccessItems as $item) {
            if(!$this->hasMenuAccess($item)) {
                $this->_redirect('error/noaccess');
                return;
            }
        }

        foreach($accessItems as $item) {
            if(!$this->hasAccess($item)) {
                $this->_redirect('error/noaccess');
                return;
            }
        }
    }

    public function hasAccess($resource) {
        $access = new Zend_Config_Ini(
                APPLICATION_PATH . '/configs/access.ini', APPLICATION_ENV);
        
        $accessArray = explode(",",$access->$resource);

        if (in_array($this->auth->online_rights, $accessArray)) {
            return true;
        }
        return false;
    }

    public function moduleAccess($resource) {
        $access = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/access.ini', APPLICATION_ENV);

        if ($access->modules->$resource == 'N') {
            return false;
        } else return true;
    }

    public function addData($tableName, $data, $returnField = false) {
        return $this->functions->saveData($tableName, $data, $where = false, $returnField);
    }
    public function saveData($tableName, $data, $where = false) {
        return $this->functions->saveData($tableName, $data, $where, $returnField);
    }


    public function log($remark, $logtype = 'default')
    {
        $obj = new Application_Model_Base();
        $obj->log($remark, $logtype);
    }

}

