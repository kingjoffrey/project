<?php

class Cli_Model_Chat
{
    public function __construct($msg, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        $db = $handler->getDb();

        switch ($handler->getType()) {
            case 'game':
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
                break;
            case 'private':
                $mChat = new Application_Model_GameChat($user->parameters['playerId'], $db);
                $mChat->insertChatMessage(1111, $msg);
                break;
        }
    }
}