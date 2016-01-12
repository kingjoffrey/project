<?php

class Cli_GeneratorOpen
{

    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_EditorHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            $this->sendError($user, 'Brak "playerId"');
            return;
        }
        if (!isset($dataIn['mapId'])) {
            $this->sendError($user, 'Brak "mapId"');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception('Brak uprawnień!');
        }

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
    }
}
