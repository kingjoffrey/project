<?php

class Cli_Model_Editor
{
    private $_mapId;
    private $_Fields;
    private $_Players;
    private $_Ruins;

    public function __construct($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_mapId = $mapId;

        $mMapFields = new Application_Model_MapFields($this->_mapId, $db);
        $this->_Fields = new Cli_Model_Fields($mMapFields->getMapFields());

        $mMapPlayers = new Application_Model_MapPlayers($this->_mapId, $db);

        $this->_Players = new Cli_Model_Players();
        $this->_Ruins = new Cli_Model_Ruins();

        $players = array(); //create new db table to store players info
        $this->initPlayers($mMapPlayers, $players, $db);
    }

    private function initPlayers(Application_Model_MapPlayers $mMapPlayers, $players, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mapCastles = $mMapCastles->getMapCastles();
        $mMapTowers = new Application_Model_MapTowers($this->_mapId, $db);
        $mapTowers = $mMapTowers->getMapTowers();

        $mTowersInGame = new Application_Model_TowersInGame($this->_id, $db); // create new db table to store players towers info
        $playersTowers = $mTowersInGame->getTowers();

        foreach ($this->_playersColors as $playerId => $color) {
            $this->_Players->addPlayer($color, new Cli_Model_EditorPlayer($players[$playerId], $this->_mapId, $mapCastles, $mapTowers, $playersTowers, $mMapPlayers, $db));
        }
        $this->_Players->addPlayer('neutral', new Cli_Model_NeutralPlayer($this, $mapCastles, $mapTowers, $playersTowers, $db));

        $this->_Players->initFields($this->_Fields);
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'fields' => $this->_Fields->toArray()
        );
    }

    /**
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @return Cli_Model_Editor
     */
    static public function getEditor(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['editor'];
    }
}