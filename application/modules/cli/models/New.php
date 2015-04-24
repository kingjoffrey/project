<?php

class Cli_Model_New
{
    private $_users = array();
    private $_games = array();

    public function addGame($gameId, $game)
    {
        $this->_games[$gameId] = new Cli_Model_NewGame($gameId, $game);
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
    private $_turnsLimit;
    private $_turnTimeLimit;
    private $_timeLimit;

    private $_players = array();

    public function __construct($gameId, $game)
    {
        $this->_id = $gameId;
        $this->_name = $game['name'];
        $this->_begin = $game['begin'];
        $this->_numberOfPlayers = $game['numberOfPlayers'];
        $this->_gameMasterId = $game['gameMasterId'];
    }

    public function addPlayer($playerId)
    {
        $this->_players[$playerId] = 1;
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
            'name' => $this->_name,
            'begin' => $this->_begin,
            'numberOfPlayers' => $this->_numberOfPlayers,
            'gameMasterId' => $this->_gameMasterId,
            'players' => $this->_players
        );
    }
}