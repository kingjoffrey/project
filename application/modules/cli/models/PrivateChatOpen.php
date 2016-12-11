<?php

class Cli_Model_PrivateChatOpen
{
    /**
     * Cli_Model_PrivateChatOpen constructor.
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_PrivateChatHandler $handler
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_PrivateChatHandler $handler)
    {
        if (!isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            echo('Brak "playerId" lub "langId' . "\n");
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            echo('Brak uprawnień!' . "\n");
            return;
        }

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
        $user->parameters['name'] = $dataIn['name'];

        $handler->addUser($dataIn['playerId'], $user);

        $mChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);
        $count = $mChat->getChatHistoryCount();

        $token = array(
            'type' => 'notification',
            'count' => $count
        );
        $handler->sendToUser($user, $token);

        $handler->addFriends($user->parameters['playerId']);

        $friendsOnline = array();
        $token = array(
            'type' => 'open',
            'id' => $user->parameters['playerId']
        );

        foreach ($handler->getFriends($user->parameters['playerId']) AS $friend) {
            foreach ($handler->getUsers() as $u) {
                if ($friend['friendId'] == $u->parameters['playerId']) {
                    $handler->sendToUser($u, $token);
                    $friendsOnline[] = $u->parameters['playerId'];
                }
            }
        }

        if ($friendsOnline) {
            $token = array(
                'type' => 'friends',
                'friends' => $friendsOnline
            );
            $handler->sendToUser($user, $token);
        }
    }
}