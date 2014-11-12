<?php

class Cli_Model_Player
{
    private $_id;

    private $_armies = array();
    private $_castles = array();
    private $_towers = array();

    private $_turnActive;
    private $_computer;
    private $_lost;
    private $_gold;
    private $_miniMapColor;
    private $_backgroundColor;
    private $_textColor;
    private $_longName;
    private $_team;

    public function __construct($player, $gameId, $mapCastles, Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $player['playerId'];

        $this->_turnActive = $player['turnActive'];
        $this->_computer = $player['computer'];
        $this->_lost = $player['lost'];
        $this->_gold = $player['gold'];
        $this->_miniMapColor = $player['minimapColor'];
        $this->_backgroundColor = $player['backgroundColor'];
        $this->_textColor = $player['textColor'];
        $this->_longName = $player['longName'];

        $this->_team = $mMapPlayers->getColorByMapPlayerId($player['team']);

        $this->initArmies($gameId, $db);
        $this->initCastles($gameId, $mapCastles, $db);
        $this->initTowers($gameId, $db);
    }

    private function initArmies($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_lost) {
            return;
        }

        $mArmy = new Application_Model_Army($gameId, $db);
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($mArmy->getPlayerArmies($this->_id) as $army) {
            $this->_armies[$army['armyId']] = new Cli_Model_Army($army);
            $this->_armies[$army['armyId']]->setHeroes($mHeroesInGame->getForMove($army['armyId']));
            $this->_armies[$army['armyId']]->setSoldiers($mSoldier->getForMove($army['armyId']));
            $this->_armies[$army['armyId']]->init();
        }
    }

    private function initCastles($gameId, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_lost) {
            return;
        }

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastleProduction = new Application_Model_CastleProduction($db);
        foreach ($mCastlesInGame->getPlayerCastles($this->_id) as $castleId => $castle) {
            $this->_castles[$castleId] = new Cli_Model_Castle($castle, $mapCastles[$castleId]);
            $this->_castles[$castleId]->setProduction($mCastleProduction->getCastleProduction($castleId));
        }
    }

    private function initTowers($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_lost) {
            return;
        }

        $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
        foreach ($mTowersInGame->getPlayerTowers($this->_id) as $tower) {
            $this->_towers[$tower['towerId']] = new Cli_Model_Tower($tower);
        }
    }

    public function toArray()
    {
        return array(
            'turnActive' => $this->_turnActive,
            'computer' => $this->_computer,
            'lost' => $this->_lost,
            'miniMapColor' => $this->_miniMapColor,
            'backgroundColor' => $this->_backgroundColor,
            'textColor' => $this->_textColor,
            'longName' => $this->_longName,
            'team' => $this->_team,
            'armies' => $this->armiesToArray(),
            'castles' => $this->castlesToArray(),
            'towers' => $this->towersToArray()
        );
    }

    public function armiesToArray()
    {
        $armies = array();
        foreach ($this->_armies as $armyId => $army) {
            $armies[$armyId] = $army->toArray();
        }
        return $armies;
    }

    public function castlesToArray()
    {
        $castles = array();
        foreach ($this->_castles as $castleId => $castle) {
            $castles[$castleId] = $castle->toArray();
        }
        return $castles;
    }

    private function towersToArray()
    {
        $towers = array();
        foreach ($this->_towers as $towerId => $tower) {
            $towers[$towerId] = $tower->toArray();
        }
        return $towers;
    }

    public function hasArmy($armyId)
    {
        return isset($this->_armies[$armyId]);
    }

    public function hasCastle($castleId)
    {
        return isset($this->_castles[$castleId]);
    }

    public function hasTower($towerId)
    {
        return isset($this->_towers[$towerId]);
    }

    public function canCastleProduceThisUnit($castleId, $unitId)
    {
        return $this->_castles[$castleId]->canProduceThisUnit($unitId);
    }

    public function getCastleCurrentProductionId($castleId)
    {
        return $this->_castles[$castleId]->getProductionId();
    }

    public function setProduction($gameId, $castleId, $unitId, $relocationToCastleId, $db)
    {
        $this->_castles[$castleId]->setProductionId($gameId, $this->_id, $castleId, $unitId, $relocationToCastleId, $db);
    }

    public function getArmy($armyId)
    {
        if ($this->hasArmy($armyId)) {
            return $this->_armies[$armyId];
        }
    }

    public function isOtherArmyAtPosition($armyId)
    {
        $army = $this->getArmy($armyId);
        foreach ($this->_armies as $id => $a) {
            if ($id == $armyId) {
                continue;
            }
            if ($a->x == $army->x && $a->y == $army->y) {
                return $id;
            }
        }
    }

    public function getTeam()
    {
        return $this->_team;
    }

    public function joinArmiesAtPosition($excludedArmyId, $gameId, $db)
    {
        $x = $this->_armies[$excludedArmyId]->getX();
        $y = $this->_armies[$excludedArmyId]->getY();

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mArmy = new Application_Model_Army($gameId, $db);

        $ids = array();

        foreach ($this->_armies as $armyId => $army) {
            if ($armyId == $excludedArmyId) {
                continue;
            }
            if ($x == $army->getX() && $y == $army->getY()) {
                $this->_armies[$excludedArmyId]->addHeroes($army->getHeroes());
                $this->_armies[$excludedArmyId]->addSoldiers($army->getSoldiers());
                unset($this->_armies[$armyId]);

                $mHeroesInGame->heroesUpdateArmyId($armyId, $excludedArmyId);
                $mSoldier->soldiersUpdateArmyId($armyId, $excludedArmyId);
                $mArmy->destroyArmy($armyId);

                $ids[] = $armyId;
            }
        }

        return array(
            'armyId' => $excludedArmyId,
            'deletedIds' => $ids
        );
    }

    public function noCastlesExists()
    {
        return !count($this->_castles);
    }

    public function noArmiesExists()
    {
        return !count($this->_armies);
    }

    public function castlesExists()
    {
        return count($this->_castles);
    }

    public function armiesExists()
    {
        return count($this->_armies);
    }

    public function setLost($lost)
    {
        $this->_lost = $lost;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getGold()
    {
        return $this->_gold;
    }

    public function setTurnActive($turnActive)
    {
        $this->_turnActive = $turnActive;
    }
}