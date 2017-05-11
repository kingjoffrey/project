#!/php -q
<?php

// Set timezone of script to UTC inorder to avoid DateTime warnings in
// vendor/zendframework/zend-log/Zend/Log/Logger.php
//date_default_timezone_set('UTC');

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

$application->getBootstrap()->bootstrap(array('date', 'config', 'modules','language'));

include_once(APPLICATION_PATH . '/modules/cli/handlers/PrivateChatHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/NewHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/EditorHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/GeneratorHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/ExecHandler.php');
include_once(APPLICATION_PATH . '/modules/cli/handlers/MainHandler.php');

$loop = \React\EventLoop\Factory::create();

// Create a logger which writes everything to the STDOUT
$logger = new \Zend\Log\Logger();
$writer = new Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);

// Create a WebSocket server
$configWS = Zend_Registry::get('config')->websockets;
$address = $configWS->aSchema . '://' . $configWS->aHost . ':' . $configWS->aPort;
$server = new WebSocketServer($address, $loop, $logger);

if ($configWS->aSchema == 'wss') {
    $context = stream_context_create();
    foreach (Zend_Registry::get('config')->ssl as $key => $val) {
//        echo $key . '=>' . $val . "\n";
        stream_context_set_option($context, 'ssl', $key, $val);
    }
    $server->setStreamContext($context);
}

// Create a router which transfers all connections to the suitable Handler class
$router = new \Devristo\Phpws\Server\UriHandler\ClientRouter($server, $logger);
$router->addRoute('#^/chat$#i', new Cli_PrivateChatHandler($logger));
$router->addRoute('#^/new$#i', new Cli_NewHandler($logger));
$router->addRoute('#^/editor$#i', new Cli_EditorHandler($logger));
$router->addRoute('#^/generator$#i', new Cli_GeneratorHandler($logger));
$router->addRoute('#^/exec$#i', new Cli_ExecHandler($logger));
$router->addRoute('#^/main$#i', new Cli_MainHandler($logger));


// Bind the server
$server->bind();

// Start the event loop
$loop->run();

?>
