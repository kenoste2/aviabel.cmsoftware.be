<?php
ini_set("auto_detect_line_endings", "1");

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);


$config = new Zend_Config_Ini(
    APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
$baseUrl = $config->baseHttp;
define('BASE_URL', $baseUrl);

Zend_Registry::set('config', $config);


/** databse */
require_once 'library/ez_sql.php';
$db = new db($config->db->user, $config->db->password, $config->db->dbfile, $config->db->charset);

if (!empty($_COOKIE['lang'])) {
    $lang = $_COOKIE['lang'];
} else {
    $lang = 'NL';
}


$application->bootstrap()
    ->run();