<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$run = true;
$queue = msg_get_queue(123402);
$unserialize = true;
$flags = 0;

while ($run) {
    msg_receive($queue, 0, $type, 1024, $data, $unserialize, $flags, $err);
    if ($err) {
        print_r($err);
    }

    $id = $data['id'];
    $dataIn = $data['msg'];

    if ($dataIn['type'] == 'open') {
        $this->open($dataIn, $user);

        new Cli_Model_CommonOpen($dataIn, $user, $this);

        $game = Cli_CommonHandler::getGameFromUser($user);
        if ($game->isActive() && $game->getPlayers()->getPlayer($game->getPlayerColor($game->getTurnPlayerId()))->getComputer()) {
            new Cli_Model_Computer($user, $this);
        }
        return;
    }

    switch ($dataIn['type']) {

    }


//    switch ($type) {
//        case 1:
//            $test1 = new MyApp\Test1($data);
//            $test1->start();
//            break;
//        case 2:
//            $test2 = new MyApp\Test2($data);
//            $test2->start();
//            break;
//    }
}