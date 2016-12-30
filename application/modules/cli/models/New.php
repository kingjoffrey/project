<?php

class Cli_Model_New
{
    private $_users = array();
    private $_games = array();

    public function addGame($gameId, $game, $gameMasterName)
    {
        $this->_games[$gameId] = new Cli_Model_NewGame($gameId, $game, $gameMasterName);
    }

    /**
     * @param $gameId
     * @return Cli_Model_NewGame
     */
    public function getGame($gameId)
    {
        if (isset($this->_games[$gameId])) {
            return $this->_games[$gameId];
        }
    }

    public function removeGame($gameId)
    {
        if (isset($this->_games[$gameId])) {
            unset($this->_games[$gameId]);
        }
    }

    public function gamesToArray()
    {
        $array = array();
        foreach (array_keys($this->_games) as $gameId) {
            $game = $this->getGame($gameId);
            $array[$gameId] = $game->toArray();
        }
        return $array;
    }

    public function addUser($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
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
     * @return Cli_Model_New
     */
    static public function getNew(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['new'];
    }
}

class Cli_Model_NewGame
{
    private $_id;
    private $_name;
    private $_begin;
    private $_numberOfPlayers;
    private $_gameMasterId;
    private $_gameMasterName;
    private $_turnsLimit;
    private $_turnTimeLimit;
    private $_timeLimit;

    private $_players = array();

    public function __construct($gameId, $game, $gameMasterName)
    {
        $this->_id = $gameId;
        $this->_name = $game['name'];
        $this->_begin = $game['begin'];
        $this->_numberOfPlayers = $game['numberOfPlayers'];
        $this->_gameMasterId = $game['gameMasterId'];
        $this->_gameMasterName = $gameMasterName;
    }

    public function addPlayer($playerId)
    {
        $this->_players[$playerId] = 1;
    }

    public function getPlayer($playerId)
    {
        if (isset($this->_players[$playerId])) {
            return $this->_players[$playerId];
        }
    }

    public function removePlayer($playerId)
    {
        if (isset($this->_players[$playerId])) {
            unset($this->_players[$playerId]);
        }
    }

    public function getPlayers()
    {
        return $this->_players;
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'name' => $this->_name,
            'begin' => $this->_begin,
            'numberOfPlayers' => $this->_numberOfPlayers,
            'gameMasterId' => $this->_gameMasterId,
            'gameMasterName' => $this->_gameMasterName,
            'players' => $this->_players
        );
    }
}

class Cli_Model_Setup
{
    private $_gameId;
    private $_isOpen = true;

    private $_setupUsers = array();
    private $_players = array();

    private $_gameMasterId;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_gameId = $gameId;

        $mGame = new Application_Model_Game($this->_gameId, $db);
        $this->_gameMasterId = $mGame->getGameMasterId();
        if (empty($this->_gameMasterId)) {
            $this->setNewGameMaster($db);
            $this->_gameMasterId = $mGame->getGameMasterId();
        }
    }

    public function update($playerId, Cli_SetupHandler $handler, $close = false)
    {
        if ($close) {
            $token = array(
                'player' => array('playerId' => $playerId),
                'gameMasterId' => $this->_gameMasterId,
                'close' => 1,
                'type' => 'update'
            );
        } else {
            $token = array(
                'player' => $this->_players[$playerId],
                'gameMasterId' => $this->_gameMasterId,
                'type' => 'update'
            );
        }

        $handler->sendToChannel($handler->getSetupGame($this->_gameId), $token);
    }

    public function getPlayerIdByMapPlayerId($mapPlayerId)
    {
        foreach ($this->_players as $playerId => $player) {
            if (isset($player['mapPlayerId']) && $player['mapPlayerId'] == $mapPlayerId) {
                return $playerId;
            }
        }
    }

    public function updatePlayerReady($playerId, $mapPlayerId)
    {
        $this->_players[$playerId]['mapPlayerId'] = $mapPlayerId;
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
            if (isset($player['mapPlayerId']) && $player['mapPlayerId'] == $mapPlayerId) {
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
        $mGame = new Application_Model_Game($this->_gameId, $db);
        $mGame->setNewGameMaster($this->_gameMasterId);
    }

    public function findNewGameMaster()
    {
        reset($this->_setupUsers);
        return key($this->_setupUsers);
    }

    public function getGameMasterId()
    {
        return $this->_gameMasterId;
    }

    public function getGameId()
    {
        return $this->_gameId;
    }

    public function addUser($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $db)
    {
        $mPlayer = new Application_Model_Player($db);
        $player = $mPlayer->getPlayer($playerId);
        $this->_players[$playerId] = array(
            'playerId' => $player['playerId'],
//            'mapPlayerId' => $player['mapPlayerId'],
            'firstName' => $player['firstName'],
            'lastName' => $player['lastName']
        );
        $this->_setupUsers[$playerId] = $user;
    }

    public function removeUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mWebSocket = new Application_Model_Websocket($user->parameters['playerId'], $db);
        $mWebSocket->disconnect($user->parameters['accessKey']);
        unset($this->_setupUsers[$user->parameters['playerId']]);
    }

    public function getSetupUsers()
    {
        return $this->_setupUsers;
    }

    public function getPlayers()
    {
        return $this->_players;
    }

    public function getIsOpen()
    {
        return $this->_isOpen;
    }

    public function setIsOpen($isOpen)
    {
        $this->_isOpen = $isOpen;
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