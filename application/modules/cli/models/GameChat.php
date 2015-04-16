<?php

class Cli_Model_GameChat
{
    public function __construct($msg, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        $db = $handler->getDb();

        $game = Cli_Model_Game::getGame($user);
        $me = Cli_Model_Me::getMe($user);
        $mChat = new Application_Model_GameChat($game->getId(), $db);
        $mChat->insertChatMessage($me->getId(), $msg);
        $game->updateChatHistory($msg, $me->getColor());

        $token = array(
            'type' => 'chat',
            'msg' => $msg,
            'color' => $me->getColor()
        );

        $handler->sendToChannel($game, $token);
    }
}
}