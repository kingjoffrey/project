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
    protected $_path;

    /**
     * @param $playerId
     * @param $computer
     */
    public function getComputerEmptyCastleInComputerRange($computer)
    {
        $this->_l->logMethodName();

        $this->_player->getComputerEmptyCastleInComputerRange($computer, $this->_fields);
    }

    public function getEnemiesHaveRangeAtThisCastle(Cli_Model_Castle $castle)
    {
        $this->_l->logMethodName();
        $enemiesHaveRange = array();
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        foreach ($this->_players->get() as $color => $player) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $mHeuristics = new Cli_Model_Heuristics($castleX, $castleY);
                $h = $mHeuristics->calculateH($enemy->getX(), $enemy->getY());
                if ($h < $enemy->getMovesLeft()) {
                    $this->_fields->setCastleTemporaryType($castleX, $castleY, 'E');
                    try {
                        $aStar = new Cli_Model_Astar($enemy, $castleX, $castleY, $this->_fields, $this->_color);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    $this->_fields->resetCastleTemporaryType($castleX, $castleY);

                    if ($enemy->unitsHaveRange($aStar->getPath($castleX . '_' . $castleY))) {
                        $enemiesHaveRange[] = $enemy;
                    }
                }
            }
        }

        return $enemiesHaveRange;
    }

    public function getEnemiesInRange()
    {
        $this->_l->logMethodName();
        $enemiesInRange = array();

        foreach ($this->_players->get() as $color => $player) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();
                $mHeuristics = new Cli_Model_Heuristics($this->_armyX, $this->_armyY);
                $h = $mHeuristics->calculateH($enemyX, $enemyY);
                if ($h < $this->_army->getMovesLeft()) {
                    $this->_fields->setTemporaryType($enemyX, $enemyY, 'E');
                    try {
                        $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_fields, $this->_color);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    $this->_fields->resetTemporaryType($enemyX, $enemyY);

                    $move = $this->_army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY));
                    if ($move->x == $enemyX && $move->y == $enemyY) {
                        $enemiesInRange[] = $enemy;
                    }
                }
            }
        }
        if (!empty($enemiesInRange)) {
            return $enemiesInRange;
        } else {
//             new Game_Logger('BRAK WROGA W ZASIĘGU ARMII');
            return false;
        }
    }

    public function getPathToNearestRuin()
    {
        $this->_l->logMethodName();

        foreach ($this->_game->getRuins()->get() as $ruinId => $ruin) {
            if ($ruin->getEmpty()) {
                continue;
            }

            $ruinX = $ruin->getX();
            $ruinY = $ruin->getY();

            $mHeuristics = new Cli_Model_Heuristics($this->_armyX, $this->_armyY);
            $h = $mHeuristics->calculateH($ruinX, $ruinY);
            if ($h < $this->_movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $ruinX, $ruinY, $this->_fields, $this->_color);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }
                $path = new Cli_Model_Path($aStar->getPath($ruinX . '_' . $ruinY), $this->_army);
                if ($path->getX() == $ruinX && $path->getY() == $ruinY) {
                    $this->_path = $path;
                    return $ruin->getId();
                }
            }
        }
    }

    public function getPathToMyCastle(Cli_Model_Castle $castle)
    {
        $this->_l->logMethodName();
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        if ($castleX == $this->_armyX && $castleY == $this->_armyY) {
            return;
        }
        try {
            $aStar = new Cli_Model_Astar($this->_army, $castleX, $castleY, $this->_fields, $this->_color);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $this->_army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY));
    }

    /**
     * @param $playerId
     * @return mixed
     *
     * todo sprawdzić jak to działa
     */
    public function getMyCastleNearEnemy()
    {
        $this->_l->logMethodName();
        $castlesHeuristics = array();

        foreach ($this->_player->getCastles() as $castleId => $castle) {
            $mHeuristics = new Cli_Model_Heuristics($castle->getX(), $castle->getY());
            foreach ($this->_players->get() as $color => $player) {
                if ($this->_players->sameTeam($this->_color, $color)) {
                    continue;
                }
                foreach ($this->_players->getPlayer($color)->getArmies() as $enemy) {
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
        return $this->_player->getCastle(key($castlesHeuristics));
    }

    protected function getPathToMyArmyInRange()
    {
        $this->_l->logMethodName();
        $turnNumber = $this->_game->getTurnNumber();
        if ($turnNumber < 5) {
            return;
        }
        $myArmies = array();

        $numberOfUnits = floor($turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }
        $numberOfSoldiersAndHeroes = count($this->_army->getNumberOfSoldiers()) + count($this->_army->getNumberOfHeroes());
        foreach ($this->_player->getArmies() as $armyId => $army) {
            if ($armyId == $this->_armyId) {
                continue;
            }
            $myArmies[$armyId] = $army;
        }

        foreach ($myArmies as $armyId => $army) {
            $numberOfSoldiers = $army->getNumberOfSoldiers();
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
                    $aStar = new Cli_Model_Astar($this->_army, $armyX, $armyY, $this->_fields, $this->_color);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                return new Cli_Model_Path($aStar->getPath($armyX . '_' . $armyY), $this->_army);
            }
        }
    }

    public function getStrongerEnemyArmyInRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            foreach ($this->_players->getPlayer($color)->getArmies() as $enemy) {
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
                        $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_fields, $this->_color);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    return new Cli_Model_Path($aStar->getPath($enemyX . '_' . $enemyY), $this->_army);
                }
            }
        }
        return null;
    }

    /**
     * @return Cli_Model_Path
     */
    public function getWeakerEnemyArmyInRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            foreach ($this->_players->getPlayer($color)->getArmies() as $enemy) {
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
                        $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_fields, $this->_color);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    return new Cli_Model_Path($aStar->getPath($enemyX . '_' . $enemyY), $this->_army);
                }
            }
        }
    }

    public function canAttackAllEnemyHaveRange($enemiesHaveRange)
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
    public function isMyCastleInRangeOfEnemy($pathToMyEmptyCastle)
    {
        $this->_l->logMethodName();

        foreach ($this->_enemies as $enemy) {
            $enemyX = $enemy->getX();
            $enemyY = $enemy->getY();
            $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
            $h = $mHeuristics->calculateH($pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y);
            if ($h < $enemy->getMovesLeft()) {
                $this->_fields->setCastleTemporaryType($pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y, 'E');
                try {
                    $aStar = new Cli_Model_Astar($enemy, $pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y, $this->_fields, $this->_color);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }
                $this->_fields->resetCastleTemporaryType($pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y);
                if ($enemy->unitsHaveRange($aStar->getPath($pathToMyEmptyCastle->x . '_' . $pathToMyEmptyCastle->y))) {
                    return true;
                }
            }
        }
    }

    public function getPathToEnemyInRange($enemy)
    {
        $this->_l->logMethodName();
        $enemyX = $enemy->getX();
        $enemyY = $enemy->getY();
        $this->_fields->setCastleTemporaryType($enemyX, $enemyY, 'E');
        try {
            $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_fields, $this->_color);
        } catch (Exception $e) {
            echo($e);
            return;
        }
        $this->_fields->resetCastleTemporaryType($enemyX, $enemyY);
        return $this->_army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY));
    }

    public function isEnemyArmyInRange($enemy)
    {
        $this->_l->logMethodName();
        $enemyX = $enemy->getX();
        $enemyY = $enemy->getY();

        if ($castleId = $this->_fields->getCastleId($this->_color, $enemyX, $enemyY)) {
            $this->_fields->setCastleTemporaryType($enemyX, $enemyY, 'E');
        } else {
            $this->_fields->setTemporaryType($enemyX, $enemyY, 'E');
        }

        try {
            $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_fields, $this->_color);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $this->_path = new Cli_Model_Path($aStar->getPath($enemyX . '_' . $enemyY), $this->_army);

        if ($castleId) {
            $this->_fields->resetCastleTemporaryType($enemyX, $enemyY);
        } else {
            $this->_fields->resetTemporaryType($enemyX, $enemyY);
        }
    }

    public function getMyEmptyCastleInMyRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_player->getCastles() as $castleId => $castle) {
            $castleX = $castle->getX();
            $castleY = $castle->getY();
            if ($this->_player->countCastleGarrison($castleId)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($this->_armyX, $this->_armyY);
            $h = $mHeuristics->calculateH($castleX, $castleY);
            if ($h < $this->_movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $castleX, $castleY, $this->_fields, $this->_color);
                } catch (Exception $e) {
                    $this->_l->log($e);
                    return;
                }

                $move = $this->_army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY));
                if ($move->x == $castleX && $move->y == $castleY) {
                    return $move;
                }
            }
        }
    }
}

