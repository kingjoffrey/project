<?php

use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_ExecOpen
{

    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_ExecHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            $l = new Coret_Model_Logger('Cli_Model_ExecOpen');
            $l->log('Brak "playerId"');
            return;
        }

        $db = Cli_Model_Database::getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            echo ('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')') . "\n";
            return;
        }

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
        $user->parameters['gameId'] = $dataIn['gameId'];
    }
}
