<?php

class Cli_Model_ExecOpen
{

    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_HelpHandler $handler)
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

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
    }
}
