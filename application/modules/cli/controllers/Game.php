<?php

class GameController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $layout = new Zend_Layout();
        $layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts');
        $layout->setLayout('game');

        $token = array(
            'type' => 'game',
            'action' => 'index',
            'data' => $layout->render(),
            'gameId' => $dataIn['gameId']
        );

        $handler->sendToUser($user, $token);
    }
}