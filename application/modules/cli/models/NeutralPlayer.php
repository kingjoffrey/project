<?php

class Cli_Model_NeutralPlayer extends Cli_Model_DefaultPlayer
{
    private $_longName = 'Shadow';

    private $_color = 'neutral';
    private $_team = 'neutral';

    public function __construct($mapId, $gameId, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();
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
            $this->_castles->addCastle($castleId, new Cli_Model_Castle(array(), $castle));
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
            $this->_towers->add($towerId, new Cli_Model_Tower($tower));
        }
    }

    public function toArray()
    {
        return array(
            'longName' => $this->_longName,
            'team' => $this->_team,
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray()
        );
    }

    public function getCastleGarrison($turnNumber, $firstUnitId, $castleId)
    {
        $castle = $this->_castles->getCastle($castleId);
        $numberOfSoldiers = ceil($turnNumber / 10);
        $units = Zend_Registry::get('units');

        $army = new Cli_Model_Army(array(
            'armyId' => 0,
            'x' => $castle->getX(),
            'y' => $castle->getY()
        ), 'neutral');
        for ($i = 1; $i <= $numberOfSoldiers; $i++) {
            $soldierId = 's' . $i;
            $army->getSoldiers()->add($soldierId, new Cli_Model_Soldier(array(
                'defensePoints' => 3,
                'soldierId' => $soldierId,
                'unitId' => $firstUnitId
            ), $units[$firstUnitId]));
        }

        return array($army);
    }

    public function getArmies()
    {
        return new Cli_Model_Armies();
    }

    public function getTeam()
    {
        return 'neutral';
    }

    public function getDefenceSequence()
    {
        return;
    }

    public function getId()
    {
        return 0;
    }
}