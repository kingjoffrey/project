<?php

class Cli_Model_PrivateChat
{
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_PrivateChatHandler $handler)
    {
        if (!isset($user->parameters['playerId'])) {
            throw new Exception('No playerId');
            return;
        }

        $db = $handler->getDb();
        $read = 'false';

        if ($friend = $handler->getUser($dataIn['friendId'])) {
            $token = array(
                'type' => 'chat',
                'msg' => strip_tags($dataIn['msg']),
                'id' => $user->parameters['playerId'],
                'name' => $user->parameters['name']
            );

            $handler->sendToUser($friend, $token);
            $read = 'true';
        }

        $mChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);
        $mChat->insertChatMessage($dataIn['friendId'], $dataIn['msg'], $read);
    }
}