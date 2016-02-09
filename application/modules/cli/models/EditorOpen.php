<?php

class Cli_Model_EditorOpen
{

    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_EditorHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            $handler->sendError($user, 'Brak "playerId"');
            return;
        }
        if (!isset($dataIn['mapId'])) {
            $handler->sendError($user, 'Brak "mapId"');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception('Brak uprawnień!');
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);

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
        $token['type'] = 'init';

        $handler->sendToUser($user, $token);
    }
}
