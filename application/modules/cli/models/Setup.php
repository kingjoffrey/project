<?php

class Cli_Model_Setup
{
    private $_id;

    private $_users = array();

    private $_gameMasterId;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $mMapPlayers = new Application_Model_MapPlayers($mGame->getMapId(), $this->_db);
        Zend_Registry::set('mapPlayerIdToShortNameRelations', $mMapPlayers->getShortNameToMapPlayerIdRelations());

        $this->_gameMasterId = $mGame->getGameMasterId();

    }

    public function update($db, $handler)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);

        $token = array(
            'players' => $mPlayersInGame->getPlayersWaitingForGame(),
            'gameMasterId' => $this->_gameMasterId,
            'type' => 'update'
        );

        $handler->sendToChannel($token, $this->_id);
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
     * @return Cli_Model_Setup
     */
    static public function getSetup(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['game'];
    }
}