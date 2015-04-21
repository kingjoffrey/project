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

    public function addGame($gameId, Cli_Model_Setup $game)
    {
        $this->_games[$gameId] = $game;
    }

    public function removeGame($gameId)
    {
        $this->_game[$gameId] = null;
        unset($this->_game[$gameId]);
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

                $this->sendToChannel(Cli_Model_Setup::getSetup($user), $token);
                break;

            case 'start':

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
                $setup->setNewGameMaster($this->_db);
                $setup->update($this);
            }

            $token = array(
                'type' => 'close',
                'playerId' => $user->parameters['playerId']
            );

            $this->sendToChannel($setup, $token);

            if (!$setup->getUsers()) {
                $this->removeGame($setup->getId());
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
     * @param Cli_Model_Setup $setup
     * @param $token
     * @param null $debug
     * @throws Zend_Exception
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
