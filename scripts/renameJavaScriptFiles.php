#!/php -q
<?php

// Set timezone of script to UTC inorder to avoid DateTime warnings in
// vendor/zendframework/zend-log/Zend/Log/Logger.php

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

$application->getBootstrap()->bootstrap(array('date', 'config'));


class JavaScript
{

    public function __construct()
    {
        $path = APPLICATION_PATH . '/../public/js/';
        $version = Zend_Registry::get('config')->version;

        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != 'admin') {
                    $array[] = $entry;
                }
            }

            asort($array);

            foreach ($array as $k => $entry) {
                if (is_dir($path . '/' . $entry)) {
                    $this->renameJavaScript($path . '/' . $entry, $version);
                }
            }

            closedir($handle);
        }

        rename(APPLICATION_PATH . '/../public/css/main.css', APPLICATION_PATH . '/../public/css/' . $version . 'main.css');
    }

    public function renameJavaScript($path, $version)
    {
        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $array[] = $entry;
                }
            }

            asort($array);

            foreach ($array as $k => $entry) {
                rename($path . '/' . $entry, $path . '/' . $version . $entry);
            }


            closedir($handle);
        }
    }
}

new JavaScript();