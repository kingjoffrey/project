<?php

class Cli_Model_PrivateChat
{
    public function __construct($msg, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_PrivateChatHandler $handler)
    {
        $db = $handler->getDb();

        $mChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);
        $mChat->insertChatMessage(1111, $msg);
    }
}