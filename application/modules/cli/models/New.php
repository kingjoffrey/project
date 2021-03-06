<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_New
{
    private $_users = array();
    private $_games = array();

    public function addGame($gameId, $game, $gameMasterName)
    {
        $this->_games[$gameId] = new NewGame($game, $gameMasterName);
    }

    /**
     * @param $gameId
     * @return NewGame
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

    public function addUser($playerId, WebSocketTransportInterface $user)
    {
        $this->_users[$playerId] = $user;
    }

    public function removeUser($playerId)
    {
        unset($this->_users[$playerId]);
    }

    public function getUsers()
    {
        return $this->_users;
    }

    /**
     * @param WebSocketTransportInterface $user
     * @return Cli_Model_New
     */
    static public function getNew(WebSocketTransportInterface $user)
    {
        return $user->parameters['new'];
    }
}

class NewGame
{
    /**
     * @var OpenGame
     */
    private $_game;
    private $_players = array();

    public function __construct(OpenGame $game, $gameMasterName)
    {
        $this->_game = $game;
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
        $array = $this->_game->toArray();
        $array['players'] = $this->_players;

        return $array;
    }
}

class OpenGame
{
    private $_gameId;
    private $_mapName;
    private $_begin;
    private $_numberOfPlayers;
    private $_gameMasterId;
    private $_gameMasterName;
    private $_isOpen = true;

    private $_users = array();
    private $_players = array();

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_gameId = $gameId;

        $mGame = new Application_Model_Game($this->_gameId, $db);
        $game = $mGame->getGame();

        $mMap = new Application_Model_Map($game['mapId'], $db);
        $this->_mapName = $mMap->getName();
        $this->_begin = $game['begin'];
        $this->_numberOfPlayers = $game['numberOfPlayers'];
        $this->_gameMasterId = $game['gameMasterId'];
    }

    public function setGameMasterName($name)
    {
        $this->_gameMasterName = $name;
    }

    public function update($playerId, Cli_OpenGamesHandler $handler, $close = false)
    {
        if ($close) {
            $token = array(
                'player' => array('playerId' => $playerId),
                'close' => 1,
                'type' => 'update'
            );
        } else {
            $token = array(
                'player' => $this->_players[$playerId],
                'type' => 'update'
            );
        }

        $handler->sendToChannel($handler->getSetupGame($this->_gameId), $token);
    }

    public function getPlayerIdBySideId($sideId)
    {
        foreach ($this->_players as $playerId => $player) {
            if (isset($player['sideId']) && $player['sideId'] == $sideId) {
                return $playerId;
            }
        }
    }

    public function updatePlayerReady($playerId, $sideId)
    {
        $this->_players[$playerId]['sideId'] = $sideId;
    }

    public function isNoComputerColorInGame($sideId)
    {
        foreach ($this->_players as $playerId => $player) {
            if ($player['sideId'] == $sideId && $player['computer'] == false) {
                return true;
            }
        }
    }

    public function isPlayer($sideId)
    {
        foreach ($this->_players as $playerId => $player) {
            if (isset($player['sideId']) && $player['sideId'] == $sideId) {
                return true;
            }
        }
    }

    public function isGameMaster($playerId)
    {
        return $playerId == $this->_gameMasterId;
    }

    public function getGameMasterId()
    {
        return $this->_gameMasterId;
    }

    public function getGameId()
    {
        return $this->_gameId;
    }

    public function addUser($playerId, WebSocketTransportInterface $user, $db)
    {
        $mPlayer = new Application_Model_Player($db);
        $player = $mPlayer->getPlayer($playerId);

        $name = trim($player['firstName'] . ' ' . $player['lastName']);

        $this->_players[$playerId] = array(
            'playerId' => $player['playerId'],
            'name' => $name
        );
        $this->_users[$playerId] = $user;
    }

    public function removeUser($playerId)
    {
        unset($this->_users[$playerId]);
    }

    public function getUsers()
    {
        return $this->_users;
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

    public function toArray()
    {
        return array(
            'id' => $this->_gameId,
            'mapName' => $this->_mapName,
            'begin' => $this->_begin,
            'numberOfPlayers' => $this->_numberOfPlayers,
            'gameMasterId' => $this->_gameMasterId,
            'gameMasterName' => $this->_gameMasterName
        );
    }

    /**
     * @param WebSocketTransportInterface $user
     * @return OpenGame
     */
    static public function getGame(WebSocketTransportInterface $user)
    {
        if (isset($user->parameters['game'])) {
            return $user->parameters['game'];
        }
    }
}