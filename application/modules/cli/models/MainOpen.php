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

        $token = array(
            'type' => 'open',
            'menu' => $handler->menu(),
        );

        $handler->sendToUser($user, $token);
    }
}
