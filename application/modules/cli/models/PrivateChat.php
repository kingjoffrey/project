<?php

class Cli_Model_PrivateChat
{
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_PrivateChatHandler $handler)
    {
        $db = $handler->getDb();
        $read = 'false';

        if ($friend = $handler->getUser($dataIn['friendId'])) {
            $token = array(
                'type' => 'chat',
                'msg' => $dataIn['msg']
            );

            $handler->sendToUser($friend, $token);
            $read = 'true';
        }

        $mChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);
        $mChat->insertChatMessage($dataIn['friendId'], $dataIn['msg'], $read);
    }
}