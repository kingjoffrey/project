<?php

class Cli_Model_New
{
    private $_users = array();
    private $_games = array();
    private $_players = array();

    public function addGame($gameId, $game) // klasa game zawierająca players
    {
        $this->_games[$gameId] = $game;
    }

    public function getGame($gameId)
    {
        if (isset($this->_games[$gameId])) {
            $this->_games[$gameId];
        }
    }

    public function removeGame($gameId)
    {
        if (isset($this->_games[$gameId])) {
            unset($this->_games[$gameId]);
        }
    }

    public function getGames()
    {
        return $this->_games;
    }

//    public function addPlayer($playerId)
//    {
//        $this->_players[$playerId] = 1;
//    }
//
//    public function removePlayer($playerId)
//    {
//        if (isset($this->_players[$playerId])) {
//            unset($this->_players[$playerId]);
//        }
//    }
//
//    public function getPlayers()
//    {
//        return $this->_players;
//    }

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