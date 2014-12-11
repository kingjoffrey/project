<?php

abstract class Cli_Model_ComputerMethods
{
    protected $_game;
    protected $_gameId;
    protected $_playerId;
    protected $_players;
    protected $_player;
    protected $_fields;
    protected $_db;
    protected $_army;
    protected $_gameHandler;
    protected $_l;

    protected function getComputerEmptyCastleInComputerRange()
    {
        $this->_l->logMethodName();
        {
            foreach ($this->_player->getCastles()->getKeys() as $castleId) {
                $castle = $this->_player->getCastles()->getCastle($castleId);
                $castleX = $castle->getX();
                $castleY = $castle->getY();

                if ($this->_fields->areArmiesInCastle($castleX, $castleY)) {
                    continue;
                }

                $mHeuristics = new Cli_Model_Heuristics($this->_armyX, $this->_armyY);
                $h = $mHeuristics->calculateH($castleX, $castleY);
                if ($h < $this->_movesLeft) {
                    try {
                        $aStar = new Cli_Model_Astar($this->_army, $castleX, $castleY, $this->_game);
                        return $aStar->path();
                    } catch (Exception $e) {
                        $this->_l->log($e);
                        return;
                    }
                }
            }
        }
    }

    protected function getEnemiesHaveRangeAtThisCastle(Cli_Model_Castle $castle)
    {
        $this->_l->logMethodName();
        $enemiesHaveRange = array();
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            foreach ($this->_players->getPlayer($color)->getArmies() as $enemy) {
                $mHeuristics = new Cli_Model_Heuristics($castleX, $castleY);
                $h = $mHeuristics->calculateH($enemy->getX(), $enemy->getY());
                if ($h < $enemy->getMovesLeft()) {
                    try {
                        $aStar = new Cli_Model_Astar($enemy, $castleX, $castleY, $this->_game);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    if ($enemy->unitsHaveRange($aStar->path())) {
                        $enemiesHaveRange[] = $enemy;
                    }
                }
            }
        }

        return $enemiesHaveRange;
    }

    protected function getEnemiesInRange()
    {
        $this->_l->logMethodName();
        $enemiesInRange = array();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            foreach ($this->_players->getPlayer($color)->getArmies() as $enemy) {
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();
                $mHeuristics = new Cli_Model_Heuristics($this->_armyX, $this->_armyY);
                $h = $mHeuristics->calculateH($enemyX, $enemyY);
                if ($h < $this->_movesLeft) {
                    try {
                        $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_game);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    $path = $aStar->path();
                    if ($path->enemyInRange()) {
                        $enemiesInRange[] = $enemy;
                    }
                }
            }
        }
        return $enemiesInRange;
    }

    protected function getPathToMyCastle(Cli_Model_Castle $castle)
    {
        $this->_l->logMethodName();
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        if ($castleX == $this->_armyX && $castleY == $this->_armyY) {
            return;
        }
        try {
            $aStar = new Cli_Model_Astar($this->_army, $castleX, $castleY, $this->_game);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $aStar->path();
    }

    /**
     * @param $playerId
     * @return mixed
     *
     * todo sprawdzić jak to działa
     */
    protected function getMyCastleNearEnemy()
    {
        $this->_l->logMethodName();
        $castlesHeuristics = array();

        $castles = $this->_player->getCasles();
        foreach ($castles->getKeys() as $castleId) {
            $castle = $castles->getCastle($castleId);
            $mHeuristics = new Cli_Model_Heuristics($castle->getX(), $castle->getY());
            foreach ($this->_players->getKeys() as $color) {
                if ($this->_players->sameTeam($this->_color, $color)) {
                    continue;
                }
                $armies = $this->_players->getPlayer($color)->getArmies();
                foreach ($armies->getKeys() as $enemyId) {
                    $enemy = $armies->getArmy($enemyId);
                    if (isset($castlesHeuristics[$castleId])) { // co tu się dzieje?
                        $castlesHeuristics[$castleId] += $mHeuristics->calculateH($enemy->getX(), $enemy->getY());
                    } else {
                        $castlesHeuristics[$castleId] = $mHeuristics->calculateH($enemy->getX(), $enemy->getY());
                    }
                }
            }
        }

        if (empty($castlesHeuristics)) {
            return;
        }

        asort($castlesHeuristics, SORT_NUMERIC);
        reset($castlesHeuristics);
        return $castles->getCastle(key($castlesHeuristics));
    }

    protected function getPathToMyArmyInRange()
    {
        $this->_l->logMethodName();
        $turnNumber = $this->_game->getTurnNumber();
        if ($turnNumber < 5) {
            return;
        }
        $myArmies = new Cli_Model_Armies();

        $numberOfUnits = floor($turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }
        $numberOfSoldiersAndHeroes = $this->_army->count();
        foreach ($this->_player->getArmies()->getKeys() as $armyId) {
            if ($armyId == $this->_armyId) {
                continue;
            }
            $myArmies->addArmy($armyId, $this->_player->getArmies()->getArmy($armyId));
        }

        foreach ($myArmies->getKeys() as $armyId) {
            $army = $myArmies->getArmy($armyId);
            $numberOfSoldiers = $army->getSoldiers()->count();
            $armyX = $army->getX();
            $armyY = $army->getY();

            if ($this->_fields->isPlayerCastle($this->_color, $armyX, $armyY)) {
                if ($numberOfUnits == $numberOfSoldiers) {
                    continue;
                }
            }

            if ($numberOfSoldiersAndHeroes > 3 * $numberOfSoldiers) {
                continue;
            }

            if ($numberOfSoldiers > 3 * $numberOfSoldiersAndHeroes) {
                continue;
            }

            $mHeuristics = new Cli_Model_Heuristics($armyX, $armyY);
            $h = $mHeuristics->calculateH($this->_armyX, $this->_armyY);
            if ($h < $this->_movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $armyX, $armyY, $this->_game);
                    return $aStar->path();
                } catch (Exception $e) {
                    echo($e);
                    return;
                }
            }
        }
    }

    protected function getStrongerEnemyArmyInRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            $armies = $this->_players->getPlayer($color)->getArmies();
            foreach ($armies->getKeys() as $enemyId) {
                $enemy = $armies->getArmy($enemyId);
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();

                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                $h = $mHeuristics->calculateH($this->_armyX, $this->_armyY);
                if ($h < $this->_movesLeft) {
                    $es = new Cli_Model_EnemyStronger($this->_army, $this->_game, $enemyX, $enemyY, $this->_color);
                    if (!$es->stronger()) {
                        continue;
                    }
                    try {
                        $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_game);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    return $aStar->path();
                }
            }
        }
        return null;
    }

    /**
     * @return Cli_Model_Path
     */
    protected function getWeakerEnemyArmyInRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            $armies = $this->_players->getPlayer($color)->getArmies();
            foreach ($armies->getKeys() as $enemyId) {
                $enemy = $armies->getArmy($enemyId);
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();
                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                $h = $mHeuristics->calculateH($this->_armyX, $this->_armyY);
                if ($h < $this->_movesLeft) {
                    $es = new Cli_Model_EnemyStronger($this->_army, $this->_game, $enemyX, $enemyY, $this->_color);
                    if ($es->stronger()) {
                        continue;
                    }

                    try {
                        $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_game);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    return $aStar->path();
                }
            }
        }
    }

    protected function canAttackAllEnemyHaveRange($enemiesHaveRange)
    {
        $this->_l->logMethodName();
        foreach ($enemiesHaveRange as $enemy) {
            if ($this->isEnemyStronger(array($enemy))) {
                return;
            }
        }
        return $enemy;
    }

    /**
     * @param $playerId
     * @param $pathToMyEmptyCastle
     * @return bool
     */
    protected function isMyCastleInRangeOfEnemy(Cli_Model_Path $pathToMyEmptyCastle)
    {
        $this->_l->logMethodName();
        $castleX = $pathToMyEmptyCastle->getX();
        $castleY = $pathToMyEmptyCastle->getY();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            $armies = $this->_players->getPlayer($color)->getArmies();
            foreach ($armies->getKeys() as $enemyId) {
                $enemy = $armies->getArmy($enemyId);
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();
                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                $h = $mHeuristics->calculateH($castleX, $castleY);
                if ($h < $enemy->getMovesLeft()) {
                    try {
                        $aStar = new Cli_Model_Astar($enemy, $castleX, $castleY, $this->_game);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }
                    if ($enemy->unitsHaveRange($aStar->path())) {
                        return true;
                    }
                }
            }
        }
    }

    protected function getPathToEnemyInRange($enemy)
    {
        $this->_l->logMethodName();
        $enemyX = $enemy->getX();
        $enemyY = $enemy->getY();
        try {
            $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_game);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $aStar->path();
    }

    /**
     * @param $enemy
     * @return Cli_Model_Path
     */
    protected function isEnemyArmyInRange(Cli_Model_Army $enemy)
    {
        $this->_l->logMethodName();
        $enemyX = $enemy->getX();
        $enemyY = $enemy->getY();

        try {
            $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_game);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $aStar->path();
    }

    protected function getMyEmptyCastleInMyRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_player->getCasles()->getKeys() as $castleId) {
            $castle = $this->_player->getCasles()->getCastle($castleId);
            $castleX = $castle->getX();
            $castleY = $castle->getY();
            if ($this->_player->countCastleGarrison($castleId)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($this->_armyX, $this->_armyY);
            $h = $mHeuristics->calculateH($castleX, $castleY);
            if ($h < $this->_movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $castleX, $castleY, $this->_game);
                } catch (Exception $e) {
                    $this->_l->log($e);
                    return;
                }

                return $aStar->path();
            }
        }
    }
}

