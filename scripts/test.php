<?php
date_default_timezone_set('UTC');

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

require_once(APPLICATION_PATH . "/../vendor/autoload.php");

$loop = \React\EventLoop\Factory::create();

$logger = new \Zend\Log\Logger();
$writer = new Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);

$address = 'ws://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort . '/test';
$client = new \Devristo\Phpws\Client\WebSocket($address, $loop, $logger);


//$client->on("connect", function () use ($logger, $client) {
//    $logger->notice("Or we can use the connect event!");
//    $client->send("Hello world!");
//});

//$client->on("message", function ($message) use ($client, $logger) {
//    $logger->notice("Got message: " . $message->getData());
//    $client->close();
//});

$client->open()->then(function () use ($logger, $client) {
    $logger->notice("We can use a promise to determine when the socket has been connected!");
});

//$loop->run();

echo 'aaa';

$run = true;
$queue = msg_get_queue(123402);

while ($run) {
    usleep(100000);
    msg_receive($queue, 0, $type, 4, $data, false, null, $err);
    echo "msgtype {$type} data {$data}\n";
    switch ($type) {
        case 1:
            $client->send(1 . date(' H:i:s'));
            $test1 = new Cli_Model_Test1();
            $test1->start();

            $token = array(
                'type' => 'proxy',
                'token' => array('type' => 'test')
            );

            $client->send(json_encode($token));
            break;
        case 2:
            $test2 = new Cli_Model_Test2();
            $test2->start();
            break;
    }
}


