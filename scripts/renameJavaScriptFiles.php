#!/php -q
<?php

// Set timezone of script to UTC inorder to avoid DateTime warnings in
// vendor/zendframework/zend-log/Zend/Log/Logger.php
date_default_timezone_set('UTC');

require_once("../vendor/autoload.php");

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

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

$application->getBootstrap()->bootstrap(array('date', 'config', 'modules'));


class javaScript
{

    public function __construct()
    {
        $this->appendJavaScript(APPLICATION_PATH . '/../public/js/', $version = Zend_Registry::get('config')->version);
    }

    public function appendJavaScript($path, $version)
    {
        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $array[] = $entry;
                }
            }

            asort($array);

            foreach ($array as $k => $entry) {
                if (is_dir($path . '/' . $entry)) {
                    $this->appendJavaScript($path . '/' . $entry, $version);
                } else {

                }
            }


            closedir($handle);
        }
    }
}
