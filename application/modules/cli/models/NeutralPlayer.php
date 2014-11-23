<?php

class Cli_Model_NeutralPlayer extends Cli_Model_DefaultPlayer
{
    public function __construct($mapId, $gameId, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->initCastles($gameId, $mapCastles, $db);
        $this->initTowers($mapId, $gameId, $db);
    }

    public function initCastles($gameId, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $playersCastles = $mCastlesInGame->getAllCastles();

        foreach ($mapCastles as $castleId => $castle) {
            if (isset($playersCastles[$castleId])) {
                continue;
            }
            $this->_castles[$castleId] = new Cli_Model_Castle(array(), $castle);
        }
    }

    public function initTowers($mapId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
        $playersTowers = $mTowersInGame->getTowers();

        $mMapTowers = new Application_Model_MapTowers($mapId, $db);
        foreach ($mMapTowers->getMapTowers() as $towerId => $tower) {
            if (isset($playersTowers[$towerId])) {
                continue;
            }
            $tower['towerId'] = $towerId;
            $this->_towers[$towerId] = new Cli_Model_Tower($tower);
        }
    }

    public function toArray()
    {
        return array(
            'castles' => $this->castlesToArray(),
            'towers' => $this->towersToArray()
        );
    }

    public function getCastleGarrison($turnNumber, $firstUnitId)
    {
        $numberOfSoldiers = ceil($turnNumber / 10);
        $soldiers = array();
        $units = Zend_Registry::get('units');

        for ($i = 1; $i <= $numberOfSoldiers; $i++) {
            $soldiers[] = new Cli_Model_Soldier(array(
                'defensePoints' => 3,
                'soldierId' => 's' . $i,
                'unitId' => $firstUnitId
            ), $units[$firstUnitId]);
        }

        $army = new Cli_Model_Army(array(
            'armyId' => 0,
            'x' => 0,
            'y' => 0
        ));
        $army->addSoldiers($soldiers);

        return $army;
    }

    public function getArmies()
    {
        return array();
    }

    public function getTeam()
    {
        return 'neutral';
    }

    public function getDefenceSequence()
    {
        return;
    }

    public function removeTower($towerId)
    {
        unset($this->_towers[$towerId]);
    }

    public function getId()
    {
        return 0;
    }
}