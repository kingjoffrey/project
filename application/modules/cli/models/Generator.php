<?php

class Cli_Model_Generator
{
    private $_mapSize = 32;
//    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db)
//    {
//    }

    function create($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db, $playerId)
    {
        $formCreate = new Application_Form_Createmap();

        if (isset($dataIn['name']) && $formCreate->isValid($dataIn)) {

            $mMap = new Application_Model_Map (0, $db);
            $mapId = $mMap->create($formCreate->getValues(), $playerId);

            $mapFields = new Application_Model_MapFields($mapId, $db);
            for ($y = 0; $y < $this->_mapSize; $y++) {
                for ($x = 0; $x < $this->_mapSize; $x++) {
                    $mapFields->add($x, $y, 'g');
                }
            }

            return array(
                'type' => 'generated'
            );
        } else {
            echo 'zdddfff';
        }
    }

    public function publish($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMap = new Application_Model_Map($dataIn['mapId'], $db);
        $mMap->publish();
    }

    function mirror($dataIn, Zend_Db_Adapter_Pdo_Pgsql $db, $playerId)
    {
        if (!isset($dataIn['mapId']) || empty($dataIn['mapId'])) {
            echo('mirror: brak mapId' . "\n");
            return;
        }

        $mMap = new Application_Model_Map ($dataIn['mapId'], $db);
        $oldMap = $mMap->get();
        $mCastles = new Application_Model_MapCastles($dataIn['mapId'], $db);
        $oldCastles = $mCastles->getMapCastles();
        $mCastlesProduction = new Application_Model_MapCastleProduction($db);
        $mTowers = new Application_Model_MapTowers($dataIn['mapId'], $db);
        $oldTowers = $mTowers->getMapTowers();
        $mRuins = new Application_Model_MapRuins($dataIn['mapId'], $db);
        $oldRuins = $mRuins->getMapRuins();

        switch ($dataIn['mirror']) {
            case 0:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorTop();
                break;
            case 1:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorRight();
                break;
            case 2:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorBottom();
                break;
            default:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorLeft();
                break;
        }

        if ($mapId = $mMap->create(array('maxPlayers' => $oldMap['maxPlayers'], 'name' => $oldMap['name'] . ' mirror'), $playerId)) {

            $mapFields = new Application_Model_MapFields($mapId, $db);
            foreach ($fields as $y => $row) {
                foreach ($row as $x => $type) {
                    $mapFields->add($x, $y, $type);
                }
            }

            $maxY = count($fields) / 2;
            $maxX = count($fields[0]);
            $maxX2 = $maxX / 2;

            $mCastles = new Application_Model_MapCastles($mapId, $db);
            foreach ($oldCastles as $castleId => $castle) {
                switch ($dataIn['mirror']) {
                    case 0:
                        $id1 = $mCastles->add($castle['x'], $castle['y'] + $maxY, $castle);
                        $y = ($maxY - ($castle['y'] * 2 + 1)) + $castle['y'] - 1;
                        $id2 = $mCastles->add($castle['x'], $y, $castle);
                        break;
                    case 1:
                        $id1 = $mCastles->add($castle['x'], $castle['y'], $castle);
                        $x = $maxX - ($castle['x'] + 1) - 1;
                        $id2 = $mCastles->add($x, $castle['y'], $castle);
                        break;
                    case 2:
                        $id1 = $mCastles->add($castle['x'], $castle['y'], $castle);
                        $y = ($maxY - ($castle['y'] + 1)) + $maxY - 1;
                        $id2 = $mCastles->add($castle['x'], $y, $castle);
                        break;
                    case 3:
                        $id1 = $mCastles->add($castle['x'] + $maxX2, $castle['y'], $castle);
                        $x = $maxX2 - ($castle['x'] * 2 + 1) + $castle['x'] - 1;
                        $id2 = $mCastles->add($x, $castle['y'], $castle);
                        break;
                }
                $production = $mCastlesProduction->getCastleProduction($castleId);
                foreach ($production as $slot) {
                    $mCastlesProduction->addCastleProduction($id1, $slot);
                    $mCastlesProduction->addCastleProduction($id2, $slot);
                }

            }

            $mTowers = new Application_Model_MapTowers($mapId, $db);
            foreach ($oldTowers as $towerId => $tower) {
                switch ($dataIn['mirror']) {
                    case 0:
                        $mTowers->add($tower['x'], $tower['y'] + $maxY);
                        $y = ($maxY - ($tower['y'] * 2 + 1)) + $tower['y'];
                        $mTowers->add($tower['x'], $y);
                        break;
                    case 1:
                        $mTowers->add($tower['x'], $tower['y']);
                        $x = $maxX - ($tower['x'] + 1);
                        $mTowers->add($x, $tower['y']);
                        break;
                    case 2:
                        $mTowers->add($tower['x'], $tower['y']);
                        $y = ($maxY - ($tower['y'] + 1)) + $maxY;
                        $mTowers->add($tower['x'], $y);
                        break;
                    case 3:
                        $mTowers->add($tower['x'] + $maxX2, $tower['y']);
                        $x = $maxX2 - ($tower['x'] * 2 + 1) + $tower['x'];
                        $mTowers->add($x, $tower['y']);
                        break;
                }
            }

            $mRuins = new Application_Model_MapRuins($mapId, $db);
            foreach ($oldRuins as $ruinId => $ruin) {
                switch ($dataIn['mirror']) {
                    case 0:
                        $mRuins->add($ruin['x'], $ruin['y'] + $maxY, $ruin['ruinId']);
                        $y = ($maxY - ($ruin['y'] * 2 + 1)) + $ruin['y'];
                        $mRuins->add($ruin['x'], $y, $ruin['ruinId']);
                        break;
                    case 1:
                        $mRuins->add($ruin['x'], $ruin['y'], $ruin['ruinId']);
                        $x = $maxX - ($ruin['x'] + 1);
                        $mRuins->add($x, $ruin['y'], $ruin['ruinId']);
                        break;
                    case 2:
                        $mRuins->add($ruin['x'], $ruin['y'], $ruin['ruinId']);
                        $y = ($maxY - ($ruin['y'] + 1)) + $maxY;
                        $mRuins->add($ruin['x'], $y, $ruin['ruinId']);
                        break;
                    case 3:
                        $mRuins->add($ruin['x'] + $maxX2, $ruin['y'], $ruin['ruinId']);
                        $x = $maxX2 - ($ruin['x'] * 2 + 1) + $ruin['x'];
                        $mRuins->add($x, $ruin['y'], $ruin['ruinId']);
                        break;
                }
            }

            return array(
                'type' => 'mirror',
                'map' => array(
                    'mapId' => $mapId,
                    'name' => $oldMap['name'] . ' mirror',
                    'maxPlayers' => $oldMap['maxPlayers'],
                    'date' => Coret_View_Helper_Formatuj::date(date('YmdHis', time()))
                )
            );
        }
    }
}