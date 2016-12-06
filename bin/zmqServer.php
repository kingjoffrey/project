<?php
namespace GameServer;
require dirname(__DIR__) . '/vendor/autoload.php';

$run = true;
$queue = msg_get_queue(123402);
$unserialize = true;
$flags = 0;

class A extends \Threaded
{
    private $_i = 0;

    public function increment()
    {
        $this->_i++;
    }

    public function getI()
    {
        return $this->_i;
    }
}

$a = new A();

class B extends \Threaded
{
    private $_i = 0;

    public function increment()
    {
        $this->_i++;
    }

    public function getI()
    {
        return $this->_i;
    }
}

$b = new B();

while ($run) {
    msg_receive($queue, 0, $type, 1024, $data, $unserialize, $flags, $err);
    if ($err) {
        print_r($err);
    }

    print_r($data);

    $id = $data['id'];
    $dataIn = $data['msg'];

    if ($dataIn->type == 'open') {
//        $this->open($dataIn, $user);
//
        $cm = new \CommonOpen($dataIn);
        $cm->start();

//
//        $game = Cli_CommonHandler::getGameFromUser($user);
//        if ($game->isActive() && $game->getPlayers()->getPlayer($game->getPlayerColor($game->getTurnPlayerId()))->getComputer()) {
//            new Cli_Model_Computer($user, $this);
//        }
//        return;
    }

    switch ($dataIn->type) {
        case 'test1':
            $t = new Test1($id, $a);
            $t->start();
            break;
        case 'test2':
            $t = new Test2($id, $b);
            $t->start();
            break;
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