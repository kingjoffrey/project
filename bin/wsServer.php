<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$handler = new GameSever\Handler();

$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555');
$pull->on('message', array($handler, 'onZMQ'));

$webSock = new React\Socket\Server($loop);
$webSock->listen(8080, '127.0.0.1');
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer($handler)
    ), $webSock);

$loop->run();
