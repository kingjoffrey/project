<?php

class Cli_Model_Player extends Cli_Model_DefaultPlayer
{
    private $_id;

    private $_armies = array();

    private $_turnActive;
    private $_computer;
    private $_lost;

    private $_gold;
    private $_income = 0;

    private $_miniMapColor;
    private $_backgroundColor;
    private $_textColor;
    private $_longName;
    private $_team;

    private $_attackSequence;
    private $_defenceSequence;

    public function __construct($player, $gameId, $mapCastles, $mapTowers, Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $player['playerId'];
        $this->_lost = $player['lost'];

        if ($this->_lost) {
            return;
        }

        $this->_turnActive = $player['turnActive'];
        $this->_computer = $player['computer'];
        $this->_gold = $player['gold'];
        $this->_miniMapColor = $player['minimapColor'];
        $this->_backgroundColor = $player['backgroundColor'];
        $this->_textColor = $player['textColor'];
        $this->_longName = $player['longName'];

        $this->_team = $mMapPlayers->getColorByMapPlayerId($player['team']);

        $this->initArmies($gameId, $db);
        $this->initCastles($gameId, $mapCastles, $db);
        $this->initTowers($gameId, $mapTowers, $db);
        $this->initBattleSequence($gameId, $db);
    }

    private function initBattleSequence($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mBattleSequence = new Application_Model_BattleSequence($gameId, $db);
        $battleSequence = $mBattleSequence->get($this->_id);
        if (isset($battleSequence['attack'])) {
            $this->_attackSequence = $battleSequence['attack'];
        }
        if (isset($battleSequence['defence'])) {
            $this->_defenceSequence = $battleSequence['defence'];
        }
    }

    private function initArmies($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
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
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastleProduction = new Application_Model_CastleProduction($db);
        foreach ($mCastlesInGame->getPlayerCastles($this->_id) as $castleId => $castle) {
            $this->_castles[$castleId] = new Cli_Model_Castle($castle, $mapCastles[$castleId]);
            $this->_castles[$castleId]->setProduction($mCastleProduction->getCastleProduction($castleId));
            $this->addIncome($this->_castles[$castleId]->getIncome());
        }
    }

    private function initTowers($gameId, $mapTowers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
        foreach ($mTowersInGame->getPlayerTowers($this->_id) as $tower) {
            $towerId = $tower['towerId'];
            $tower = $mapTowers[$towerId];
            $tower['towerId'] = $towerId;
            $this->_towers[$towerId] = new Cli_Model_Tower($tower);
            $this->addIncome(5);
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

    public function hasArmy($armyId)
    {
        return isset($this->_armies[$armyId]);
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

    public function getArmies()
    {
        return $this->_armies;
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

        return $ids;
    }

    public function noArmiesExists()
    {
        return !count($this->_armies);
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

    public function addIncome($income)
    {
        $this->_income += $income;
    }

    public function subtractIncome($income)
    {
        $this->_income -= $income;
    }

    public function subtractGold($gold)
    {
        $this->_gold -= $gold;
    }

    public function addGold($gold, $gameId, $db)
    {
        $this->_gold += $gold;
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mPlayersInGame->updatePlayerGold($this->_id, $this->_gold);
    }

    public function setTurnActive($turnActive)
    {
        $this->_turnActive = $turnActive;
    }

    public function startTurn($gameId, $turnNumber, $db)
    {
        $units = Zend_Registry::get('units');

        $this->resetMovesLeft($gameId, $db);

        foreach ($this->_armies as $armyId => $army) {
            $this->subtractGold($army->getCosts());
        }

        $this->addGold(count($this->_towers) * 5, $this->_id, $db);

        foreach ($this->_castles as $castleId => $castle) {
            $this->addGold($castle->getIncome(), $this->_id, $db);
            $production = $castle->getProduction();

            if ($this->_computer) {
                if ($turnNumber < 7) {
                    $unitId = $castle->getUnitIdWithShortestProductionTime($production);
                } else {
                    $unitId = $castle->findBestCastleProduction();
                }
                if ($unitId != $castle->getProductionId()) {
                    $relocationToCastleId = null;
                    $castle->setProductionId($gameId, $this->_id, $castleId, $unitId, $relocationToCastleId, $db);
                }
            } else {
                $unitId = $castle->getProductionId();
            }

            if ($unitId && $production[$unitId]['time'] <= $castle->getProductionTurn() && $units[$unitId]['cost'] <= $this->_gold) {
                $castle->resetProductionTurn($gameId, $db);
                $unitCastleId = null;

                if ($relocationCastleId = $castle->getRelocationCastleId()) {
                    if (isset($this->_castles[$relocationCastleId])) {
                        $unitCastleId = $relocationCastleId;
                    }

                    if (!$unitCastleId) {
                        $castle->cancelProductionRelocation($gameId, $db);
                    }
                }

                if (!$unitCastleId) {
                    $unitCastleId = $castleId;
                }

                $x = $this->_castles[$unitCastleId]->getX();
                $y = $this->_castles[$unitCastleId]->getY();
                $armyId = $this->getPlayerArmyIdFromPosition($x, $y);

                if (!$armyId) {
                    $armyId = $this->createArmy($gameId, $this->_id, $x, $y, $db);
                }

                $this->_armies[$armyId]->createSoldier($gameId, $db);
            }
        }
    }

    public function getArmyIdFromPosition($x, $y)
    {
        foreach ($this->_armies as $armyId => $army) {
            if ($x == $army->getX() && $y == $army->getY()) {
                return $armyId;
            }
        }
    }

    public function createArmy($gameId, $playerId, $x, $y, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armyId = $mArmy->createArmy(array('x' => $x, 'y' => $y), $playerId);
        $this->_armies[$armyId] = new Cli_Model_Army(array(
            'x' => $x,
            'y' => $y,
            'armyId' => $armyId
        ));
    }

    public function addTower($towerId, Cli_Model_Tower $tower)
    {
        $this->addIncome(5);
        $this->_towers[$towerId] = $tower;
    }

    public function removeTower($towerId)
    {
        $this->subtractIncome(5);
        unset($this->_towers[$towerId]);
    }

    public function resetMovesLeft($gameId, $db)
    {
        foreach ($this->_armies as $army) {
            $army->resetMovesLeft($gameId, $db);
        }
    }

    public function unfortifyArmies($gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mArmy->unfortifyPlayerArmies($this->_id);

        foreach ($this->_armies as $army) {
            $army->setFortified(false, $gameId, $db);
        }
    }

    public function getComputer()
    {
        return $this->_computer;
    }

    public function getComputerArmyToMove()
    {
        foreach ($this->_armies as $armyId => $army) {
            if ($army->getFortified()) {
                continue;
            }
            return $army;
        }
    }

    public function getTurnActive()
    {
        return $this->_turnActive;
    }

    public function getComputerEmptyCastleInComputerRange($computer, $fields)
    {
        foreach ($this->_castles as $castle) {
            $castleX = $castle->getX();
            $castleY = $castle->getY();
            $color = $fields->getCastleColor($castleX, $castleY);

            if ($fields->areUnitsAtCastlePosition($castleX, $castleY)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($computer->getX(), $computer->getY());
            $h = $mHeuristics->calculateH($castleX, $castleY);
            if ($h < $computer->getMovesLeft()) {
                try {
                    $aStar = new Cli_Model_Astar($computer, $castleX, $castleY, $fields, $color);
                } catch (Exception $e) {
                    $this->_l->log($e);
                    return;
                }

                $move = $computer->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY));
                if ($move->x == $castleX && $move->y == $castleY) {
                    return $move;
                }
            }
        }
    }

    public function getCastleGarrison($castleId)
    {
        $garrison = array();
        $x = $this->_castles[$castleId]->getX();
        $y = $this->_castles[$castleId]->getY();

        foreach ($this->_armies as $armyId => $army) {
            for ($i = $y; $i <= $y + 1; $i++) {
                for ($j = $x; $j <= $x + 1; $j++) {
                    if ($army->getX() == $i && $army->getY() == $j) {
                        $garrison[$armyId] = $army;
                    }
                }
            }
        }

        return $garrison;
    }

    public function countCastleGarrison($castleId)
    {
        $count = 0;
        $x = $this->_castles[$castleId]->getX();
        $y = $this->_castles[$castleId]->getY();

        foreach ($this->_armies as $armyId => $army) {
            for ($i = $y; $i <= $y + 1; $i++) {
                for ($j = $x; $j <= $x + 1; $j++) {
                    if ($army->getX() == $i && $army->getY() == $j) {
                        $count += $army->count();
                    }
                }
            }
        }
        return $count;
    }

    public function getCastleDefenseModifier($castleId)
    {
        return $this->_castles[$castleId]->getDefenseModifier();
    }

    public function getAttackSequence()
    {
        return $this->_attackSequence;
    }

    public function getDefenceSequence()
    {
        return $this->_defenceSequence;
    }
}