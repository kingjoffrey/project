<?php

class Cli_Model_NewOpen
{
    /**
     * @param $dataIn
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_GameHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_NewHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            throw new Exception('Brak "playerId"');
        }
        if (!isset($dataIn['langId'])) {
            throw new Exception('Brak langId');
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception('Brak uprawnień!');
        }

        if (!($user->parameters['new'] = $handler->getNew())) {
            echo 'not set' . "\n";
            $handler->addNew(new Cli_Model_New());
            $user->parameters['new'] = $handler->getNew();
        }

        $new = Cli_Model_New::getNew($user);
        $new->addUser($dataIn['playerId'], $user);

        if (isset($dataIn['gameMasterId']) && isset($dataIn['gameId'])) { //setup
            if ($dataIn['gameMasterId'] == $dataIn['playerId']) {
                if (!$game = $new->getGame($dataIn['gameId'])) {
                    $mGame = new Application_Model_Game($dataIn['gameId'], $db);
                    $game = $mGame->getOpen($dataIn['gameMasterId']);
                    $new->addGame($dataIn['gameId'], $game);
                }

                $token = array(
                    'type' => 'add',
                    'game' => $game
                );
            } else {
                $token = array(
                    'type' => 'open',
                    'playerId' => $dataIn['playerId']
                );
            }
            $new->getGame($dataIn['gameId'])->addPlayer($dataIn['playerId']);
            $user->parameters['gameId'] = $dataIn['gameId'];
            $handler->sendToChannelExceptPlayers($new, $token);
        } else { //new
            $token = array(
                'type' => 'games',
                'games' => $new->gamesToArray()
            );

            $handler->sendToUser($user, $token);
        }

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
    }
}