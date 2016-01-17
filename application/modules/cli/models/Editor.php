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
        $this->_Ruins = new Cli_Model_EditorRuins();

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
        $this->_Players->addPlayer('neutral', new Cli_Model_EditorNeutralPlayer($mapCastles, $mapTowers));
        $this->_Players->initFields($this->_Fields);
    }

    private function initRuins(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($this->_mapId, $db);
        foreach ($mMapRuins->getMapRuins() as $ruinId => $position) {
            $this->_Ruins->editorAdd($ruinId, new Cli_Model_EditorRuin($position['x'], $position['y']));
            $this->_Fields->getField($position['x'], $position['y'])->setRuin($ruinId);
        }
    }

    public function toArray()
    {
        return array(
            'fields' => $this->_Fields->toArray(),
            'players' => $this->_Players->toArray(),
            'ruins' => $this->_Ruins->toArray()
        );
    }

    public function add($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        switch ($dataIn['itemName']) {
            case 'castle':
                $castle = new Cli_Model_EditorCastle($dataIn['x'], $dataIn['y']);
                $castle->create($dataIn['mapId'], $db);
                $this->_Players->getPlayer('neutral')->getCastles()->addCastle($castle->getId(), $castle);
                break;
            case 'tower':
                $tower = new Cli_Model_EditorTower($dataIn['x'], $dataIn['y']);
                $tower->create($dataIn['mapId'], $db);
                $this->_Players->getPlayer('neutral')->getTowers()->add($tower->getId(), $tower);
                break;
            case 'ruin':
                $ruin = new Cli_Model_EditorRuin($dataIn['x'], $dataIn['y']);
                $ruin->create($dataIn['mapId'], $db);
                $this->_Ruins->editorAdd($ruin->getId(), $ruin);
                break;
            case 'forest':

                break;
        }
    }

    public function remove($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        switch ($dataIn['itemName']) {
            case 'castle':
                $mMapCastles = new Application_Model_MapCastles($dataIn['mapId'], $db);
                $mMapCastles->remove($dataIn['x'], $dataIn['y']);
                break;
            case 'tower':

                break;
            case 'ruin':

                break;
            case 'forest':

                break;
        }
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