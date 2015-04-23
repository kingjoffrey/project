#!/php -q
<?php

// Set timezone of script to UTC inorder to avoid DateTime warnings in
// vendor/zendframework/zend-log/Zend/Log/Logger.php
date_default_timezone_set('UTC');

require_once("../vendor/autoload.php");

// Run from command prompt > php chat.php
use Devristo\Phpws\Server\IWebSocketServerObserver;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;
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

$application->getBootstrap()->bootstrap(array('date', 'config', 'modules', 'frontController'));

include_once(APPLICATION_PATH . '/modules/cli/handlers/PrivateChatHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/GameHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/SetupHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/NewHandler.php');

$loop = \React\EventLoop\Factory::create();

// Create a logger which writes everything to the STDOUT
$logger = new \Zend\Log\Logger();
$writer = new Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);

// Create a WebSocket server
$server = new WebSocketServer('tcp://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort, $loop, $logger);

// Create a router which transfers all /chat connections to the ChatHandler class
$router = new \Devristo\Phpws\Server\UriHandler\ClientRouter($server, $logger);

// route /chat url
//$router->addRoute('#^/chat$#i', new ChatHandler($logger));

$router->addRoute('#^/chat$#i', new Cli_PrivateChatHandler($logger));
$router->addRoute('#^/game$#i', new Cli_GameHandler($logger));
$router->addRoute('#^/setup$#i', new Cli_SetupHandler($logger));
$router->addRoute('#^/new$#i', new Cli_NewHandler($logger));

// Bind the server
$server->bind();

// Start the event loop
$loop->run();

?>
