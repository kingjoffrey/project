<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

/**
 * This resource handler will respond to all messages sent to /public on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_NewHandler extends WebSocketUriHandler
{

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            if (!isset($dataIn['gameId']) || !isset($dataIn['playerId'])) {
                $this->sendError($user, 'Brak "gameId" lub "playerId"');
                return;
            }

            $mPlayersInGame = new Application_Model_PlayersInGame($dataIn['gameId'], $db);

            if (!$mPlayersInGame->checkAccessKey($dataIn['playerId'], $dataIn['accessKey'])) {
                $this->sendError($user, 'Brak uprawnień!');
                return;
            }

            $user->parameters = array(
                'gameId' => $dataIn['gameId'],
                'playerId' => $dataIn['playerId']
            );

            $mPlayersInGame->updateWSSUId($dataIn['playerId'], $user->getId());
            $this->update($dataIn['gameId'], $db);

            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);

            $mMapPlayers = new Application_Model_MapPlayers($mGame->getMapId(), $db);
            Zend_Registry::set('mapPlayerIdToShortNameRelations', $mMapPlayers->getShortNameToMapPlayerIdRelations());

            return;
        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'team':
                $token = array(
                    'type' => 'team',
                    'mapPlayerId' => $dataIn['mapPlayerId'],
                    'teamId' => $dataIn['teamId']
                );

                $this->sendToChannel($db, $token, $user->parameters['gameId']);
                break;

            case 'start':
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);

                if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                    echo('Not game master!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
                $mPlayersInGame->disconnectNotActive();

                $players = $mPlayersInGame->getAll();

                $mapId = $mGame->getMapId();

                $mMapCastles = new Application_Model_MapCastles($mapId, $db);
                $startPositions = $mMapCastles->getDefaultStartPositions();

                $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
                $mapPlayers = $mMapPlayers->getAll();

                $first = true;

                foreach ($mapPlayers as $mapPlayerId => $mapPlayer) {
                    if (isset($players[$mapPlayerId])) {
                        $playerId = $players[$mapPlayerId]['playerId'];
                    } else {
                        $playerId = $mPlayersInGame->getComputerPlayerId();
                        if (!$playerId) {
                            $modelPlayer = new Application_Model_Player($db);
                            $playerId = $modelPlayer->createComputerPlayer();
                            $modelHero = new Application_Model_Hero($playerId, $db);
                            $modelHero->createHero();
                        }
                        $mPlayersInGame->joinGame($playerId);
                        $mPlayersInGame->updatePlayerReady($playerId, $mapPlayerId);
                    }

                    if ($first) {
                        $mTurn = new Application_Model_TurnHistory($user->parameters['gameId'], $db);
                        $mTurn->add($playerId, 1);
                        $mGame->startGame($playerId);
                        $first = false;
                    }

                    $mPlayersInGame->setTeam($playerId, $dataIn['team'][$mapPlayerId]);

                    $mHero = new Application_Model_Hero($playerId, $db);
                    $playerHeroes = $mHero->getHeroes();
                    if (empty($playerHeroes)) {
                        $mHero->createHero();
                        $playerHeroes = $mHero->getHeroes($playerId, $db);
                    }
                    $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);

                    $armyId = $mArmy->createArmy($startPositions[$mapPlayer['castleId']], $playerId);

                    $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
                    $mHeroesInGame->add($armyId, $playerHeroes[0]['heroId']);

                    $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
                    $mCastlesInGame->addCastle($mapPlayer['castleId'], $playerId);
                }

                $token = array('type' => 'start');

                $this->sendToChannel($db, $token, $user->parameters['gameId']);
                break;

            case 'change':
                $mapPlayerId = $dataIn['mapPlayerId'];

                if (empty($mapPlayerId)) {
                    echo('Brak mapPlayerId!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);

                if ($mPlayersInGame->getMapPlayerIdByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db) == $mapPlayerId) { // unselect
                    $mPlayersInGame->updatePlayerReady($user->parameters['playerId'], $mapPlayerId);
                } elseif (!$mPlayersInGame->isNoComputerColorInGame($mapPlayerId)) { // select
                    if ($mPlayersInGame->isColorInGame($mapPlayerId)) {
                        $mPlayersInGame->updatePlayerReady($mPlayersInGame->getPlayerIdByMapPlayerId($mapPlayerId), $mapPlayerId);
                    }
                    $mPlayersInGame->updatePlayerReady($user->parameters['playerId'], $mapPlayerId);
                } elseif ($mGame->isGameMaster($user->parameters['playerId'])) { // kick
                    $mPlayersInGame->updatePlayerReady($mPlayersInGame->getPlayerIdByMapPlayerId($mapPlayerId), $mapPlayerId);
                } else {
                    echo('Błąd!');
                    return;
                }

                $this->update($user->parameters['gameId'], $db);
                break;

            case 'computer':
                $mapPlayerId = $dataIn['mapPlayerId'];

                if (empty($mapPlayerId)) {
                    echo('Brak mapPlayerId!');
                    return;
                }

                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
                if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                    echo('Brak uprawnień!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);

                if ($mPlayersInGame->isColorInGame($mapPlayerId)) {
                    echo('Ten kolor jest już w grze!');
                    return;
                }

                $playerId = $mPlayersInGame->getComputerPlayerId();

                if (!$playerId) {
                    $mPlayer = new Application_Model_Player($db);
                    $playerId = $mPlayer->createComputerPlayer();

                    $mHero = new Application_Model_Hero($playerId, $db);
                    $mHero->createHero();
                }

                if (!$mPlayersInGame->isPlayerInGame($playerId)) {
                    $mPlayersInGame->joinGame($playerId);
                }
                $mPlayersInGame->updatePlayerReady($playerId, $mapPlayerId);

                $this->update($user->parameters['gameId'], $db);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        if (!isset($user->parameters['gameId']) || !isset($user->parameters['playerId'])) {
            return;
        }
        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            return;
        }

        $db = Cli_Model_Database::getDb();

        $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
        if ($mGame->isGameStarted()) {
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $mPlayersInGame->updateWSSUId($user->parameters['playerId'], null);

        $mGame->setNewGameMaster($mPlayersInGame->findNewGameMaster());
        $this->update($user->parameters['gameId'], $db);
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

    /**
     * @param $user
     * @param $msg
     */
    public function sendError($user, $msg)
    {
        $token = array(
            'type' => 'error',
            'msg' => $msg
        );

        $this->sendToUser($user, $token);
    }

    /**
     * @param $user
     * @param $token
     * @param null $debug
     */
    public function sendToUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ');
            print_r($token);
        }

        $user->sendString(Zend_Json::encode($token));
    }

    /**
     * @param $db
     * @param $token
     * @param $gameId
     * @param null $debug
     */
    public function sendToChannel($db, $token, $gameId, $debug = null)
    {
        echo Zend_Registry::get('config')->debug;
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $users = $mPlayersInGame->getInGameWSSUIds();

        foreach ($users AS $row) {
            foreach ($this->users AS $u) {
                if ($u->getId() == $row['webSocketServerUserId']) {
                    $this->sendToUser($u, $token);
                }
            }
        }
    }
}