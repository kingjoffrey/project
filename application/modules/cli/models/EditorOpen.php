<?php

class Cli_EditorOpen
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

        if (!($user->parameters['editor'] = $handler->getEditor($dataIn['mapId']))) {
            echo 'not set' . "\n";
            $handler->addEditor($dataIn['mapId'], new Cli_Model_Editor());
            $user->parameters['editor'] = $handler->getEditor();
        }

//        $game = Cli_Model_Game::getGame($user);
//        $game->addUser($dataIn['playerId'], $user);


        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

        $mMapFields = new Application_Model_MapFields($dataIn['mapId'], $db);
        $fields = new Cli_Model_Fields($mMapFields->getMapFields());

        $token = array(
            'fields' => $fields->toArray()
        );

        $this->sendToUser($user, $token);
    }
}
