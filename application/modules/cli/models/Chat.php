<?php

class Cli_Model_Chat
{

    public function __construct($msg, $user, $handler)
    {
        $game = Cli_Model_Game::getGame($user);
        $me = Cli_Model_Me::getMe($user);

        $db = $handler->getDb();
        $mChat = new Application_Model_Chat($game->getId(), $db);
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