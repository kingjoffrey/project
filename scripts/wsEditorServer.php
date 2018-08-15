#!/php -q
<?php

// Set timezone of script to UTC inorder to avoid DateTime warnings in
// vendor/zendframework/zend-log/Zend/Log/Logger.php
date_default_timezone_set('UTC');

require_once("../vendor/autoload.php");

use Devristo\Phpws\Server\IWebSocketServerObserver;
use Devristo\Phpws\Server\WebSocketServer;

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

$application->getBootstrap()->bootstrap(array('date', 'config', 'modules', 'language'));

include_once(APPLICATION_PATH . '/modules/cli/handlers/EditorHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/GeneratorHandler.php');

$loop = \React\EventLoop\Factory::create();

// Create a logger which writes everything to the STDOUT
$logger = new \Zend\Log\Logger();
$writer = new Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);

// Create a WebSocket server
$configWS = Zend_Registry::get('config')->websockets;
$port = $configWS->aPort + 1;
$address = $configWS->aSchema . '://' . $configWS->aHost . ':' . $port;
$server = new WebSocketServer($address, $loop, $logger);

if ($configWS->aSchema == 'wss') {
    $context = stream_context_create();
    foreach (Zend_Registry::get('config')->ssl as $key => $val) {
        stream_context_set_option($context, 'ssl', $key, $val);
    }
    $server->setStreamContext($context);
}

// Create a router which transfers all connections to the suitable Handler class
$router = new \Devristo\Phpws\Server\UriHandler\ClientRouter($server, $logger);
$router->addRoute('#^/editor$#i', new Cli_EditorHandler($logger));
$router->addRoute('#^/generator$#i', new Cli_GeneratorHandler($logger));

// Bind the server
$server->bind();

// Start the event loop
$loop->run();
