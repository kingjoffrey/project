<?php

/**
 * This resource handler will respond to all messages sent to /public on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_EditorHandler extends Cli_WofHandler
{

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            if (!isset($dataIn['playerId'])) {
                $this->sendError($user, 'Brak "playerId"');
                return;
            }

            $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

            if (!$mWebSocket->auth($dataIn['accessKey'], $dataIn['websocketId'])) {
                $this->sendError($user, 'Brak uprawnień!');
                return;
            }

            $user->parameters = array(
                'websocketId' => $dataIn['websocketId'],
                'playerId' => $dataIn['playerId']
            );

            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "playerId". Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'save':
                echo 'aaa';

                $map = str_replace('data:image/png;base64,', '', $dataIn['map']);
                $map = str_replace(' ', '+', $map);
                $data = base64_decode($map);
                $file = APPLICATION_PATH . '/../public/img/maps/' . $dataIn['mapId'] . '.png';
                $success = file_put_contents($file, $data);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user)
    {
        if ($user->parameters['playerId'] && $user->parameters['websocketId']) {
            $db = Cli_Model_Database::getDb();
            $mWebSocket = new Application_Model_Websocket($user->parameters['playerId'], $db);
            $mWebSocket->disconnect($user->parameters['websocketId']);
        }
    }

    private function update($gameId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mGame = new Application_Model_Game($gameId, $db);

        $token = array(
            'players' => $mPlayersInGame->getPlayersWaitingForGame(),
            'gameMasterId' => $mGame->getGameMasterId(),
            'type' => 'update'
        );

        $this->sendToChannel($db, $token, $gameId);
    }

}
