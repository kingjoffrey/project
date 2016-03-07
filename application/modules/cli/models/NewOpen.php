<?php

class Cli_Model_NewOpen
{
    /**
     * Cli_Model_NewOpen constructor.
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_NewHandler $handler
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
                    $new->addGame($dataIn['gameId'], $game, $dataIn['name']);
                }

                $new->getGame($dataIn['gameId'])->addPlayer($dataIn['playerId']);
                $token = array(
                    'type' => 'addGame',
                    'game' => $new->getGame($dataIn['gameId'])->toArray()
                );
            } else {
                $new->getGame($dataIn['gameId'])->addPlayer($dataIn['playerId']);
                $token = array(
                    'type' => 'addPlayer',
                    'playerId' => $dataIn['playerId'],
                    'gameId' => $dataIn['gameId']
                );
            }
            $user->parameters['gameId'] = $dataIn['gameId'];
            $handler->sendToChannelExceptPlayers($new, $token);
        } else { //new
            $token = array(
                'type' => 'games',
                'games' => $new->gamesToArray()
            );
            $handler->sendToUser($user, $token);

            $token = array(
                'type' => 'open',
                'id' => $dataIn['playerId'],
                'name' => $dataIn['name']
            );
            $handler->sendToChannelExceptUser($user, $new, $token);
        }

        $user->parameters['name'] = $dataIn['name'];
        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
    }
}