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
            echo ('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')') . "\n";
            return;
        }

        $mapFields = new Application_Model_MapFields($dataIn['mapId'], $db);
        foreach ($dataIn['fields'] as $y => $row) {
            foreach ($row as $x => $type) {
                $mapFields->add($x, $y, $type);
            }
        }

        $token = array('type' => 'open');
        $handler->sendToUser($user, $token);
    }
}
