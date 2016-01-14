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


        $this->_Players = new Cli_Model_Players();
        $this->_Ruins = new Cli_Model_Ruins();

        $this->initPlayers($db);
        $this->initRuins($db);
    }

    private function initPlayers(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapPlayers = new Application_Model_MapPlayers($this->_mapId, $db);
        $mapPlayers = $mMapPlayers->getAll();

        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mapCastles = $mMapCastles->getMapCastles();

        $mMapTowers = new Application_Model_MapTowers($this->_mapId, $db);
        $mapTowers = $mMapTowers->getMapTowers();

        foreach ($mapPlayers as $id => $player) {
            $this->_Players->addPlayer($player['shortName'], new Cli_Model_EditorPlayer($player, $this->_mapId, $mapCastles, $mapTowers, $mMapPlayers, $db));
        }
        $this->_Players->addPlayer('neutral', new Cli_Model_NeutralPlayer($this, $mapCastles, $mapTowers, array(), $db));
        $this->_Players->initFields($this->_Fields);
    }

    private function initRuins(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($this->_mapId, $db);
        foreach ($mMapRuins->getMapRuins() as $ruinId => $position) {
            $position['ruinId'] = $ruinId;
            $this->_Ruins->add($ruinId, new Cli_Model_Ruin($position, false));
            $this->_Fields->getField($position['x'], $position['y'])->setRuin($ruinId);
        }
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'fields' => $this->_Fields->toArray(),
            'players' => $this->_Players->toArray(),
            'ruins' => $this->_Ruins->toArray()
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