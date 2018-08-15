<?php

class Cli_Model_Editor
{
    private $_mapId;
    private $_Fields;
    private $_Players;
    private $_Ruins;
    private $_Units;

    public function __construct($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_mapId = $mapId;

        $mMapFields = new Application_Model_MapFields($this->_mapId, $db);
        $this->_Fields = new Cli_Model_Fields($mMapFields->getMapFields());


        $this->_Players = new Cli_Model_Players();
        $this->_Ruins = new Cli_Model_EditorRuins();
        $this->_Units = new Cli_Model_Units();

        $this->initPlayers($db);
        $this->initRuins($db);
        $this->initUnits($db);
    }

    private function initUnits(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mUnit = new Application_Model_Unit($db);
        foreach ($mUnit->getUnits() as $unit) {
            $this->_Units->add($unit['unitId'], new Cli_Model_Unit($unit));
        }
    }

    private function initPlayers(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mapCastles = $mMapCastles->getMapCastles();

        $mSide = new Application_Model_Side(0, $db);
        $mMap = new Application_Model_Map($this->_mapId, $db);

        $mMapTowers = new Application_Model_MapTowers($this->_mapId, $db);

        foreach ($mSide->getWithLimit($mMap->getMaxPlayers()) as $player) {
            $this->_Players->addPlayer($player['shortName'], new Cli_Model_EditorPlayer($player, $mapCastles, $db));
        }
        $this->_Players->addPlayer('neutral', new Cli_Model_EditorNeutralPlayer($mapCastles, $mMapTowers->getMapTowers(), $db));
        $this->_Players->initFields($this->_Fields);
    }

    private function initRuins(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($this->_mapId, $db);
        foreach ($mMapRuins->getMapRuins() as $mapRuinId => $mapRuin) {
            $mapRuin['mapRuinId'] = $mapRuinId;
            $this->_Ruins->add($mapRuinId, new Cli_Model_EditorRuin($mapRuin));
            $this->_Fields->getField($mapRuin['x'], $mapRuin['y'])->setRuin($mapRuinId);
        }
    }

    public function toArray()
    {
        return array(
            'fields' => $this->_Fields->toArray(),
            'players' => $this->_Players->toArray(),
            'ruins' => $this->_Ruins->toArray(),
            'units' => $this->_Units->toArray()
        );
    }

    public function up($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        switch ($this->_Fields->getField($dataIn['x'], $dataIn['y'])->getType()) {
            case 'w':
                $type = 'g';
                break;
            case 'g':
                $type = 'h';
                break;
            case 'h':
                $type = 'm';
                break;
            default:
                return array(
                    'type' => 0
                );
        }
        $this->editTerrainType($dataIn['x'], $dataIn['y'], $type, $db);
        return array(
            'type' => $type,
            'x' => $dataIn['x'],
            'y' => $dataIn['y']
        );
    }

    public function down($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        switch ($this->_Fields->getField($dataIn['x'], $dataIn['y'])->getType()) {
            case 'g':
                $type = 'w';
                break;
            case 'h':
                $type = 'g';
                break;
            case 'm':
                $type = 'h';
                break;
            default:
                return array(
                    'type' => 0
                );
        }
        $this->editTerrainType($dataIn['x'], $dataIn['y'], $type, $db);
        return array(
            'type' => $type,
            'x' => $dataIn['x'],
            'y' => $dataIn['y']
        );
    }

    public function add($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $field = $this->_Fields->getField($dataIn['x'], $dataIn['y']);
        $type = $field->getType();

        switch ($type) {
            case 'r':
                switch ($dataIn['itemName']) {
                    case 'tower':
                        $tower = new Cli_Model_EditorTower($dataIn['x'], $dataIn['y']);
                        $tower->create($this->_mapId, $db);
                        $this->_Players->getPlayer('neutral')->getTowers()->add($tower->getId(), $tower);
                        $field = $this->_Fields->getField($dataIn['x'], $dataIn['y']);
                        $field->setTower($tower->getId(), 'neutral');
                        return array(
                            'type' => 'towerId',
                            'value' => $tower->getId()
                        );
                        break;
                    case 'ruin':
                        break;
                }
                break;
            case 'g':
                switch ($dataIn['itemName']) {
                    case 'castle':
                        if ($field->getCastleId()) {
                            break;
                        }
                        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
                        $mCNG = new Cli_Model_CastleNameGenerator();

                        $castleId = $mMapCastles->add($dataIn['x'], $dataIn['y'], array('name' => $mCNG->generateCastleName()));

                        $castle = new Cli_Model_EditorCastle(null, $mMapCastles->getCastle($castleId));

                        $this->_Players->getPlayer('neutral')->getCastles()->addCastle($castle->getId(), $castle);
                        $this->_Fields->initCastle($castle->getX(), $castle->getY(), $castle->getId(), 'neutral');

                        return array(
                            'type' => 'castle',
                            'value' => $castle->toArray()
                        );
                    case 'tower':
                        $tower = new Cli_Model_EditorTower($dataIn['x'], $dataIn['y']);
                        $tower->create($this->_mapId, $db);
                        $this->_Players->getPlayer('neutral')->getTowers()->add($tower->getId(), $tower);
                        $field = $this->_Fields->getField($dataIn['x'], $dataIn['y']);
                        $field->setTower($tower->getId(), 'neutral');
                        return array(
                            'type' => 'towerId',
                            'value' => $tower->getId()
                        );
                    case 'ruin':
                        $ruin = new Cli_Model_EditorRuin($dataIn);
                        $ruin->create($this->_mapId, $db);
                        $this->_Ruins->add($ruin->getId(), $ruin);
                        $this->_Fields->getField($dataIn['x'], $dataIn['y'])->setRuin($ruin->getId());
                        return array(
                            'type' => 'ruinAdd',
                            'id' => $ruin->getId()
                        );
                    case 'forest':
                        $this->editTerrainType($dataIn['x'], $dataIn['y'], 'f', $db);
                        return array(
                            'type' => 'f',
                            'x' => $dataIn['x'],
                            'y' => $dataIn['y']
                        );
                    case 'road':
                        $this->editTerrainType($dataIn['x'], $dataIn['y'], 'r', $db);
                        return array(
                            'type' => 'r',
                            'x' => $dataIn['x'],
                            'y' => $dataIn['y']
                        );
                    case 'swamp':
                        $this->editTerrainType($dataIn['x'], $dataIn['y'], 's', $db);
                        return array(
                            'type' => 's',
                            'x' => $dataIn['x'],
                            'y' => $dataIn['y']
                        );
                }
                break;
            case 'w':
                switch ($dataIn['itemName']) {
                    case 'road':
                        $this->editTerrainType($dataIn['x'], $dataIn['y'], 'b', $db);
                        return array(
                            'type' => 'b',
                            'x' => $dataIn['x'],
                            'y' => $dataIn['y']
                        );
                }
                break;
        }
        return array(
            'type' => 0
        );
    }

    private function editTerrainType($x, $y, $type, $db)
    {
        $this->_Fields->getField($x, $y)->setType($type);
        $mMapFields = new Application_Model_MapFields($this->_mapId, $db);
        $mMapFields->edit($x, $y, $type);
    }

    public function editRuin($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($ruin = $this->_Ruins->getRuin($dataIn['mapRuinId'])) {
            $ruin->setType($dataIn['ruinId'], $this->_mapId, $db);

            return array(
                'type' => 'editRuin',
                'id' => $dataIn['mapRuinId'],
                'ruinId' => $dataIn['ruinId']
            );
        }
    }

    public function editCastle($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->_Players->getKeys() as $color) {
            foreach ($this->_Players->getPlayer($color)->getCastles()->getKeys() as $castleId) {
                if ($dataIn['castleId'] == $castleId) {
                    $castle = $this->_Players->getPlayer($color)->getCastles()->getCastle($castleId);

                    if ($dataIn['color'] != $color) {
                        $this->_Players->getPlayer($color)->getCastles()->removeCastle($castleId);
                        $this->_Players->getPlayer($dataIn['color'])->getCastles()->addCastle($castleId, $castle);
                        $this->_Fields->initCastle($castle->getX(), $castle->getY(), $castleId, $dataIn['color']);
                    }

                    $castle->edit($this->_mapId, $dataIn, $db, $this->_Players->getPlayer($dataIn['color'])->getId());

                    return array(
                        'type' => 'editCastle',
                        'castle' => $castle->toArray(),
                        'color' => $dataIn['color']
                    );
                }
            }
        }
    }

    public function remove($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $field = $this->_Fields->getField($dataIn['x'], $dataIn['y']);
        $type = $field->getType();

        switch ($type) {
            case 'f':
                return $this->grass($dataIn['mapId'], $dataIn['x'], $dataIn['y'], $field, $db);
            case 's':
                return $this->grass($dataIn['mapId'], $dataIn['x'], $dataIn['y'], $field, $db);
            case 'r':
                return $this->grass($dataIn['mapId'], $dataIn['x'], $dataIn['y'], $field, $db);
            case 'b':
                return $this->water($dataIn['mapId'], $dataIn['x'], $dataIn['y'], $field, $db);
            case 'g':
                if ($castleId = $field->getCastleId()) {
                    $castleColor = $field->getCastleColor();
                    $mMapCastles = new Application_Model_MapCastles($dataIn['mapId'], $db);
                    $mMapCastles->remove($castleId);

                    $castle = $this->_Players->getPlayer($castleColor)->getCastles()->getCastle($castleId);
                    $this->_Players->getPlayer($castleColor)->getCastles()->removeCastle($castleId);
                    $this->_Fields->razeCastle($castle->getX(), $castle->getY());
                    return array(
                        'type' => 'remove',
                        'x' => $dataIn['x'],
                        'y' => $dataIn['y']
                    );
                } elseif ($towerId = $field->getTowerId()) {
                    $mMapTowers = new Application_Model_MapTowers($dataIn['mapId'], $db);
                    $mMapTowers->remove($towerId);
                    $this->_Players->getPlayer($field->getTowerColor())->getTowers()->removeTower($towerId);
                    $field->setTower(null, null);
                    return array(
                        'type' => 'remove',
                        'x' => $dataIn['x'],
                        'y' => $dataIn['y']
                    );
                } elseif ($ruinId = $field->getRuinId()) {
                    $mMapRuins = new Application_Model_MapRuins($dataIn['mapId'], $db);
                    $mMapRuins->remove($ruinId);
                    $this->_Ruins->remove($ruinId);
                    $field->setRuin(null);
                    return array(
                        'type' => 'remove',
                        'x' => $dataIn['x'],
                        'y' => $dataIn['y']
                    );
                }
        }

        return array(
            'type' => 0
        );

    }

    private function grass($mapId, $x, $y, Cli_Model_Field $field, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapFields = new Application_Model_MapFields($mapId, $db);
        $mMapFields->edit($x, $y, 'g');
        $field->setType('g');
        return array(
            'type' => 'grass',
            'x' => $x,
            'y' => $y
        );

    }

    private function water($mapId, $x, $y, Cli_Model_Field $field, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapFields = new Application_Model_MapFields($mapId, $db);
        $mMapFields->edit($x, $y, 'w');
        $field->setType('w');
        return array(
            'type' => 'water',
            'x' => $x,
            'y' => $y
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