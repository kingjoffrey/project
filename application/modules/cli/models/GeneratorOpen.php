<?php

class Cli_Model_GeneratorOpen
{

    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GeneratorHandler $handler)
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

        $map = str_replace('data:image/png;base64,', '', $dataIn['map']);
        $map = str_replace(' ', '+', $map);
        $file = APPLICATION_PATH . '/../public/img/maps/' . $dataIn['mapId'] . '.png';
        $success = file_put_contents($file, base64_decode($map));
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
