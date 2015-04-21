<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_SetupHandler extends WebSocketUriHandler
{
    private $_db;
    private $_games = array();

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function getGames()
    {
        return $this->_games;
    }

    public function addGame($gameId, Cli_Model_Setup $game)
    {
        $this->_games[$gameId] = $game;
    }

    /**
     * @param $gameId
     * @return Cli_Model_Setup
     */
    public function getGame($gameId)
    {
        if (isset($this->_games[$gameId])) {
            return $this->_games[$gameId];
        }
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());

//        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
//            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
//            return;
//        }

        switch ($dataIn['type']) {
            case 'open':
                new Cli_Model_SetupOpen($dataIn, $user, $this);
                break;
            case 'team':
                $token = array(
                    'type' => 'team',
                    'mapPlayerId' => $dataIn['mapPlayerId'],
                    'teamId' => $dataIn['teamId']
                );

                $this->sendToChannel($token, $user->parameters['gameId']);
                break;

            case 'start':
                $mGame = new Application_Model_Game($user->parameters['gameId'], $this->_db);

                if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                    echo('Not game master!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $this->_db);
                $mPlayersInGame->disconnectNotActive();

                $players = $mPlayersInGame->getAll();

                $mapId = $mGame->getMapId();

                $mMapCastles = new Application_Model_MapCastles($mapId, $this->_db);
                $startPositions = $mMapCastles->getDefaultStartPositions();

                $mMapPlayers = new Application_Model_MapPlayers($mapId, $this->_db);
                $mapPlayers = $mMapPlayers->getAll();

                $first = true;

                foreach ($mapPlayers as $mapPlayerId => $mapPlayer) {
                    if (isset($players[$mapPlayerId])) {
                        $playerId = $players[$mapPlayerId]['playerId'];
                    } else {
                        $playerId = $mPlayersInGame->getComputerPlayerId();
                        if (!$playerId) {
                            $modelPlayer = new Application_Model_Player($this->_db);
                            $playerId = $modelPlayer->createComputerPlayer();
                            $modelHero = new Application_Model_Hero($playerId, $this->_db);
                            $modelHero->createHero();
                        }
                        $mPlayersInGame->joinGame($playerId);
                        $mPlayersInGame->updatePlayerReady($playerId, $mapPlayerId);
                    }

                    if ($first) {
                        $mTurn = new Application_Model_TurnHistory($user->parameters['gameId'], $this->_db);
                        $mTurn->add($playerId, 1);
                        $mGame->startGame($playerId);
                        $first = false;
                    }

                    $mPlayersInGame->setTeam($playerId, $dataIn['team'][$mapPlayerId]);

                    $mHero = new Application_Model_Hero($playerId, $this->_db);
                    $playerHeroes = $mHero->getHeroes();
                    if (empty($playerHeroes)) {
                        $mHero->createHero();
                        $playerHeroes = $mHero->getHeroes($playerId, $this->_db);
                    }
                    $mArmy = new Application_Model_Army($user->parameters['gameId'], $this->_db);

                    $armyId = $mArmy->createArmy($startPositions[$mapPlayer['castleId']], $playerId);

                    $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $this->_db);
                    $mHeroesInGame->add($armyId, $playerHeroes[0]['heroId']);

                    $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $this->_db);
                    $mCastlesInGame->addCastle($mapPlayer['castleId'], $playerId);
                }

                $token = array('type' => 'start');

                $this->sendToChannel($token, $user->parameters['gameId']);
                break;

            case 'change':
                $mapPlayerId = $dataIn['mapPlayerId'];

                if (empty($mapPlayerId)) {
                    echo('Brak mapPlayerId!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $this->_db);
                $mGame = new Application_Model_Game($user->parameters['gameId'], $this->_db);

                if ($mPlayersInGame->getMapPlayerIdByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $this->_db) == $mapPlayerId) { // unselect
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

                $this->update($user->parameters['gameId'], $this->_db);
                break;

            case 'computer':
                $mapPlayerId = $dataIn['mapPlayerId'];

                if (empty($mapPlayerId)) {
                    echo('Brak mapPlayerId!');
                    return;
                }

                $mGame = new Application_Model_Game($user->parameters['gameId'], $this->_db);
                if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                    echo('Brak uprawnień!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $this->_db);

                if ($mPlayersInGame->isColorInGame($mapPlayerId)) {
                    echo('Ten kolor jest już w grze!');
                    return;
                }

                $playerId = $mPlayersInGame->getComputerPlayerId();

                if (!$playerId) {
                    $mPlayer = new Application_Model_Player($this->_db);
                    $playerId = $mPlayer->createComputerPlayer();

                    $mHero = new Application_Model_Hero($playerId, $this->_db);
                    $mHero->createHero();
                }

                if (!$mPlayersInGame->isPlayerInGame($playerId)) {
                    $mPlayersInGame->joinGame($playerId);
                }
                $mPlayersInGame->updatePlayerReady($playerId, $mapPlayerId);

                $this->update($user->parameters['gameId'], $this->_db);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        $setup = Cli_Model_Setup::getSetup($user);
        if ($setup) {

//        $mGame = new Application_Model_Game($user->parameters['gameId'], $this->_db);
//        if ($mGame->isGameStarted()) {
//            return;
//        }

            $setup->removeUser($user, $this->_db);

            if ($setup->getGameMasterId() == $user->parameters['playerId']) {
                $setup->setNewGameMaster($user->parameters['gameId'], $this->_db);
                $setup->update($user->parameters['gameId'], $this->_db);
            }

            $token = array(
                'type' => 'close',
                'playerId' => $user->parameters['playerId']
            );

            $this->sendToChannel($setup, $token);

            if (!$game->getUsers()) {
                $this->removeGame($game->getId());
            }
        }
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
     * @param $token
     * @param $gameId
     * @param null $debug
     */
    public function sendToChannel(Cli_Model_Setup $setup, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($setup->getUsers() AS $user) {
            $this->sendToUser($user, $token);
        }
    }
}
