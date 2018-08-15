<?php

use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_EditorOpen
{

    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_EditorHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            $l = new Coret_Model_Logger('Cli_Model_EditorOpen');
            $l->log('Brak "playerId"');
            return;
        }
        if (!isset($dataIn['mapId'])) {
            $l = new Coret_Model_Logger('Cli_Model_EditorOpen');
            $l->log('Brak "mapId"');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            $l = new Coret_Model_Logger('Cli_Model_EditorOpen');
            $l->log('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')');
            return;
        }

        new Cli_Model_Language($dataIn['langId'], $db);

        if (!($user->parameters['editor'] = $handler->getEditor($dataIn['mapId']))) {
            echo 'not set' . "\n";
            $handler->addEditor($dataIn['mapId'], new Cli_Model_Editor($dataIn['mapId'], $db));
            $user->parameters['editor'] = $handler->getEditor($dataIn['mapId']);
        }

        $editor = Cli_Model_Editor::getEditor($user);
//
        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

        $token = $editor->toArray();
        $token['type'] = 'open';

        $handler->sendToUser($user, $token);
    }
}