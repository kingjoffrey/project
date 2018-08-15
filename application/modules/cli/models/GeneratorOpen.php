<?php

use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_GeneratorOpen
{

    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_GeneratorHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            $l = new Coret_Model_Logger('Cli_Model_GeneratorOpen');
            $l->log('Brak "playerId"');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            $l = new Coret_Model_Logger('Cli_Model_GeneratorOpen');
            echo ('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')') . "\n";
            return;
        }

        new Cli_Model_Language($dataIn['langId'], $db);

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

//        $token = array('type' => 'open');
//        $handler->sendToUser($user, $token);
    }
}