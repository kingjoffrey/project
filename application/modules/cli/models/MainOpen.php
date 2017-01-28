<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_MainOpen
{

    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            $handler->sendError($user, 'Brak "playerId"');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            echo ('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')') . "\n";
            return;
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

        $mFriend = new Application_Model_Friends($db);
        $friends = $mFriend->getFriends($user->parameters['playerId']);

        $friendsFormatted = array();
        foreach ($friends as $row) {
            $friendsFormatted[$row['friendId']] = $row['firstName'] . ' ' . $row['lastName'];
        }

        $token = array(
            'type' => 'open',
            'menu' => $handler->menu(),
            'friends' => $friendsFormatted,
        );

        $handler->sendToUser($user, $token);
    }
}
