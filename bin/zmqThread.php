<?php
require dirname(__DIR__) . '/vendor/autoload.php';

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

$run = true;
$queue = msg_get_queue(123402);
$unserialize = true;
$flags = 0;

while ($run) {
    msg_receive($queue, $argv[1], $type, 1024, $data, $unserialize, $flags, $err);
    if ($err) {
        print_r($err);
    }

    print_r($data);

    $id = $data['id'];
    $dataIn = $data['msg'];

    if ($dataIn->type == 'open') {
//        $this->open($dataIn, $user);
//
        $cm = new CommonOpen(new Data($dataIn));
        $cm->start();

//
//        $game = Cli_CommonHandler::getGameFromUser($user);
//        if ($game->isActive() && $game->getPlayers()->getPlayer($game->getPlayerColor($game->getTurnPlayerId()))->getComputer()) {
//            new Cli_Model_Computer($user, $this);
//        }
//        return;
    }

}