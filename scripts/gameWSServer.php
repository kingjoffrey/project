#!/php -q
<?php

// Set timezone of script to UTC inorder to avoid DateTime warnings in
// vendor/zendframework/zend-log/Zend/Log/Logger.php
date_default_timezone_set('UTC');

require_once("../vendor/autoload.php");

use Devristo\Phpws\Server\IWebSocketServerObserver;
use Devristo\Phpws\Server\WebSocketServer;

defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->getBootstrap()->bootstrap(array('date', 'config', 'modules'));

include_once(APPLICATION_PATH . '/modules/cli/handlers/CommonHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/GameHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/TutorialHandler.php');

$loop = \React\EventLoop\Factory::create();

// Create a logger which writes everything to the STDOUT
$logger = new \Zend\Log\Logger();
$writer = new Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);

// Create a WebSocket server
$address = 'tcp://' . Zend_Registry::get('config')->websockets->aHost . ':' . $argv[2];
$server = new WebSocketServer($address, $loop, $logger);

// Create a router which transfers all connections to the suitable Handler class
$router = new \Devristo\Phpws\Server\UriHandler\ClientRouter($server, $logger);
$router->addRoute('#^/game' . $argv[1] . '$#i', new Cli_GameHandler($logger));
$router->addRoute('#^/tutorial' . $argv[1] . '$#i', new Cli_TutorialHandler($logger));

// Bind the server
$server->bind();

// Start the event loop
$loop->run();