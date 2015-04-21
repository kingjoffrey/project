<?php

class Cli_Model_Setup
{
    private $_id;

    private $_users = array();
    private $_online = array();

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $mMapPlayers = new Application_Model_MapPlayers($mGame->getMapId(), $this->_db);
        Zend_Registry::set('mapPlayerIdToShortNameRelations', $mMapPlayers->getShortNameToMapPlayerIdRelations());
    }

    public function getId()
    {
        return $this->_id;
    }

    public function addUser($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Application_Model_PlayersInGame $mPlayersInGame)
    {
        $mPlayersInGame->updateWSSUId($playerId, $user->getId());
        $this->_users[$playerId] = $user;
        $this->updateOnline($this->getPlayerColor($playerId), 1);
    }

    public function removeUser($playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $mPlayersInGame->updateWSSUId($playerId, null);
        unset($this->_users[$playerId]);
        $this->updateOnline($this->getPlayerColor($playerId), 0);
    }

    public function getUsers()
    {
        return $this->_users;
    }

    private function updateOnline($color, $online)
    {
        $this->_online[$color] = $online;
    }

    /**
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @return Cli_Model_Game
     */
    static public function getGame(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['game'];
    }
}