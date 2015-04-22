<?php

class Cli_Model_Setup
{
    private $_id;

    private $_users = array();
    private $_players = array();

    private $_gameMasterId;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $this->_gameMasterId = $mGame->getGameMasterId();
        if (empty($this->_gameMasterId)) {
            $this->setNewGameMaster($db);
            $this->_gameMasterId = $mGame->getGameMasterId();
        }
    }

    public function update($playerId, Cli_SetupHandler $handler)
    {
        $token = array(
            'player' => $this->_players[$playerId],
            'gameMasterId' => $this->_gameMasterId,
            'type' => 'update'
        );

        $handler->sendToChannel($handler->getGame($this->_id), $token);
    }

    public function getPlayerIdByMapPlayerId($mapPlayerId)
    {
        foreach ($this->_players as $playerId => $player) {
            if ($player['mapPlayerId'] == $mapPlayerId) {
                return $playerId;
            }
        }
    }

    public function updatePlayerReady($playerId, $mapPlayerId, $mPlayersInGame)
    {
        $this->_players[$playerId]['mapPlayerId'] = $mapPlayerId;
        $mPlayersInGame->updatePlayerReady($playerId, $mapPlayerId);
    }

    public function isNoComputerColorInGame($mapPlayerId)
    {
        foreach ($this->_players as $playerId => $player) {
            if ($player['mapPlayerId'] == $mapPlayerId && $player['computer'] == false) {
                return true;
            }
        }
    }

    public function isPlayer($mapPlayerId)
    {
        foreach ($this->_players as $playerId => $player) {
            if ($player['mapPlayerId'] == $mapPlayerId) {
                return true;
            }
        }
    }

    public function isGameMaster($playerId)
    {
        return $playerId == $this->_gameMasterId;
    }

    public function setNewGameMaster($db)
    {
        $this->_gameMasterId = $this->findNewGameMaster();
        $mGame = new Application_Model_Game($this->_id, $db);
        $mGame->setNewGameMaster($this->_gameMasterId);
    }

    public function findNewGameMaster()
    {
        return key($this->_users);
    }

    public function getGameMasterId()
    {
        return $this->_gameMasterId;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function addUser($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $this->_players[$playerId] = $mPlayersInGame->getPlayerWaitingForGame($playerId);
        $this->_users[$playerId] = $user;
    }

    public function removeUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mWebSocket = new Application_Model_Websocket($user->parameters['playerId'], $db);
        $mWebSocket->disconnect($user->parameters['accessKey']);
        unset($this->_users[$user->parameters['playerId']]);
    }

    public function getUsers()
    {
        return $this->_users;
    }

    /**
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @return Cli_Model_Setup
     */
    static public function getSetup(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['game'];
    }
}