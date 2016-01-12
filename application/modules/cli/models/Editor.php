<?php

class Cli_Model_Editor
{
    private $_users = array();
    private $_editors = array();

    public function addEditor($mapId, $editor)
    {
        $this->_editors[$mapId] = new Cli_Model_NewGame($mapId, $editor);
    }

    /**
     * @param $mapId
     * @return Cli_Model_NewGame
     */
    public function getEditor($mapId)
    {
        if (isset($this->_editors[$mapId])) {
            return $this->_editors[$mapId];
        }
    }

    public function removeEditor($mapId)
    {
        if (isset($this->_editors[$mapId])) {
            unset($this->_editors[$mapId]);
        }
    }

    public function gamesToArray()
    {
        $array = array();
        foreach (array_keys($this->_editors) as $mapId) {
            $editor = $this->getEditor($mapId);
            $array[$mapId] = $editor->toArray();
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

    private $_players = array();

    public function __construct($mapId, $editor)
    {
        $this->_id = $mapId;
        $this->_name = $editor['name'];
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
            'players' => $this->_players
        );
    }
}