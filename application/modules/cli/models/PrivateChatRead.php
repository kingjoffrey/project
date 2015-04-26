<?php

class Cli_Model_PrivateChatRead
{
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_PrivateChatHandler $handler)
    {
        if (!isset($user->parameters['playerId'])) {
            throw new Exception('No playerId');
            return;
        }

        $db = $handler->getDb();
        $mChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);
        $message = $mChat->readChatMessage($dataIn['chatId'], $dataIn['read']);

        $token = array(
            'type' => 'read',
            'name' => $dataIn['name'],
            'msg' => $message
        );

        $handler->sendToUser($user, $token);
    }
}