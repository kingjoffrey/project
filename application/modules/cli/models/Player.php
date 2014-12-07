<?php

class Cli_Model_Player extends Cli_Model_DefaultPlayer
{
    private $_id;

    private $_armies;

    private $_turnActive;
    private $_computer;
    private $_lost;

    private $_gold;
    private $_income = 0;

    private $_miniMapColor;
    private $_backgroundColor;
    private $_textColor;
    private $_longName;

    private $_color;
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
        $this->_color = $player['color'];

        $this->_armies = new Cli_Model_Armies();
        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();

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

        foreach ($mArmy->getPlayerArmies($this->_id) as $a) {
            $this->_armies->addArmy($a['armyId'], new Cli_Model_Army($a, $this->_color));
            $army = $this->_armies->getArmy($a['armyId']);
            $army->setHeroes($mHeroesInGame->getForMove($a['armyId']));
            $army->setSoldiers($mSoldier->getForMove($a['armyId']));
        }
    }

    private function initCastles($gameId, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastleProduction = new Application_Model_CastleProduction($db);
        foreach ($mCastlesInGame->getPlayerCastles($this->_id) as $castleId => $c) {
            $this->_castles->addCastle($castleId, new Cli_Model_Castle($c, $mapCastles[$castleId]));
            $castle = $this->_castles->getCastle($castleId);
            $castle->setProduction($mCastleProduction->getCastleProduction($castleId));
            $this->addIncome($castle->getIncome());
        }
    }

    private function initTowers($gameId, $mapTowers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
        foreach ($mTowersInGame->getPlayerTowers($this->_id) as $tower) {
            $towerId = $tower['towerId'];
            $tower = $mapTowers[$towerId];
            $tower['towerId'] = $towerId;
            $this->_towers->add($towerId, new Cli_Model_Tower($tower));
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
            'armies' => $this->_armies->toArray(),
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray()
        );
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

    public function getArmies()
    {
        return $this->_armies;
    }

    public function getCastles()
    {
        return $this->_castles;
    }

    public function getTowers()
    {
        return $this->_towers;
    }

    public function getTeam()
    {
        return $this->_team;
    }

    public function setLost($gameId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mPlayersInGame->setPlayerLostGame($this->_id);
        $this->_lost = true;
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

        $this->_armies->resetMovesLeft($gameId, $db);

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

                $x = $this->_castles->getCastle($unitCastleId)->getX();
                $y = $this->_castles->getCastle($unitCastleId)->getY();
                $armyId = $this->getArmies()->getArmyIdFromPosition($x, $y);

                if (!$armyId) {
                    $armyId = $this->createArmy($gameId, $this->_id, $x, $y, $db);
                }

                $this->_armies->getArmy($armyId)->createSoldier($gameId, $this->_id, $unitId, $db);
            }
        }
    }

    public function createArmy($gameId, $x, $y, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armyId = $mArmy->createArmy(array('x' => $x, 'y' => $y), $this->_id);
        $army = array(
            'x' => $x,
            'y' => $y,
            'armyId' => $armyId);
        $this->_armies->addArmy($armyId, new Cli_Model_Army($army, $this->_color));
    }

    public function addCastle($castleId, Cli_Model_Castle $castle, $oldColor, Cli_Model_Fields $fields, $gameId, $db)
    {
        $this->addIncome($castle->getIncome());
        $this->_castles->addCastle($castleId, $castle, $oldColor, $this->_id, $gameId, $db);
        $fields->changeCastle($castle->getX(), $castle->getY(), $this->_color);
    }

    public function removeCastle($castleId)
    {
        $this->subtractIncome($this->_castles->getCastle($castleId)->getIncome());
        parent::removeCastle($castleId);
    }

    public function addTower($towerId, Cli_Model_Tower $tower, $oldColor, Cli_Model_Fields $fields, $gameId, $db)
    {
        $this->addIncome(5);
        $fields->changeTower($tower->getX(), $tower->getY(), $this->_color);
        $this->_towers->add($towerId, $tower, $oldColor, $this->_id, $gameId, $db);
    }

    public function removeTower($towerId)
    {
        $this->subtractIncome(5);
        parent::removeTower($towerId);
    }

    public function unfortifyArmies($gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mArmy->unfortifyPlayerArmies($this->_id);

        $this->_armies->unfortify($gameId, $db);
    }

    public function getComputer()
    {
        return $this->_computer;
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

//    public function getCastleGarrison($castleId)
//    {
//        $garrison = array();
//        $x = $this->_castles[$castleId]->getX();
//        $y = $this->_castles[$castleId]->getY();
//
//        foreach ($this->_armies as $armyId => $army) {
//            for ($i = $y; $i <= $y + 1; $i++) {
//                for ($j = $x; $j <= $x + 1; $j++) {
//                    if ($army->getX() == $i && $army->getY() == $j) {
//                        $garrison[$armyId] = $army;
//                    }
//                }
//            }
//        }
//
//        return $garrison;
//    }

//    public function countCastleGarrison($castleId)
//    {
//        $count = 0;
//        $x = $this->_castles[$castleId]->getX();
//        $y = $this->_castles[$castleId]->getY();
//
//        foreach ($this->_armies as $armyId => $army) {
//            for ($i = $y; $i <= $y + 1; $i++) {
//                for ($j = $x; $j <= $x + 1; $j++) {
//                    if ($army->getX() == $i && $army->getY() == $j) {
//                        $count += $army->count();
//                    }
//                }
//            }
//        }
//        return $count;
//    }

    public function getAttackSequence()
    {
        return $this->_attackSequence;
    }

    public function getDefenceSequence()
    {
        return $this->_defenceSequence;
    }

    public function noArmiesAndCastles()
    {
        return $this->_castles->noCastlesExists() && $this->_armies->noArmiesExists();
    }

    public function armiesOrCastlesExists()
    {
        return $this->_armies->armiesExists() || $this->_castles->castlesExists();
    }

    public function increaseAllCastlesProductionTurn($gameId, $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->increaseAllCastlesProductionTurn($this->_id);
    }
}