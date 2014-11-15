<?php

class Cli_Model_ComputerFunctions extends Cli_Model_ComputerFight
{
    protected function getPathToEnemyCastleInRange($castleId)
    {
        $this->_l->logMethodName();
        $mapCastles = Zend_Registry::get('castles');
        $position = $mapCastles[$castleId]['position'];
        $fields = Application_Model_Board::changeCastleFields($this->_map['fields'], $position['x'], $position['y'], 'E');

        try {
            $aStar = new Cli_Model_Astar($this->_Computer, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $this->_Computer->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
        if (Application_Model_Board::isCastleField($move->end, $position)) {
            $move->in = true;
        } else {
            $move->in = false;
        }

        return $move;
    }

    protected function getPathToEnemyInRange($enemy)
    {
        $this->_l->logMethodName();
        $fields = Application_Model_Board::changeCastleFields($this->_map['fields'], $enemy->x, $enemy->y, 'E');

        try {
            $aStar = new Cli_Model_Astar($this->_Computer, $enemy->x, $enemy->y, $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $this->_Computer->calculateMovesSpend($aStar->getPath($enemy->x . '_' . $enemy->y));
    }

    protected function isEnemyArmyInRange($enemy)
    {
        $this->_l->logMethodName();
        $fields = $this->_map['fields'];

        $castleId = Application_Model_Board::isCastleAtPosition($enemy->x, $enemy->y, $this->_map['hostileCastles']);
        if ($castleId) {
            $fields = Application_Model_Board::changeCastleFields($fields, $enemy->x, $enemy->y, 'E');
        } else {
            $fields = Application_Model_Board::restoreField($fields, $enemy->x, $enemy->y);
        }

        try {
            $aStar = new Cli_Model_Astar($this->_Computer, $enemy->x, $enemy->y, $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $this->_Computer->calculateMovesSpend($aStar->getPath($enemy->x . '_' . $enemy->y));
        if ($castleId) {
            if ($castleId == Application_Model_Board::isCastleAtPosition($move->x, $move->y, $this->_map['hostileCastles'])) {
                $move->castleId = $castleId;
                return $move;
            } else {
                $move->current = null;
                return $move;
            }
        } else {
            if ($move->x == $enemy->x && $move->y == $enemy->y) {
                return $move;
            } else {
                $move->current = null;
                return $move;
            }
        }
    }

    protected function getMyEmptyCastleInMyRange()
    {
        $this->_l->logMethodName();
        foreach ($this->_map['myCastles'] as $castle) {
            $position = $castle['position'];
            if ($this->_mArmyDB->areUnitsAtCastlePosition($position)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($this->_Computer->x, $this->_Computer->y);
            $h = $mHeuristics->calculateH($position['x'], $position['y']);
            if ($h < $this->_Computer->movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_Computer, $position['x'], $position['y'], $this->_map['fields']);
                } catch (Exception $e) {
                    $this->_l->log($e);
                    return;
                }

                $move = $this->_Computer->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
                if ($move->x == $position['x'] && $move->y == $position['y']) {
                    return $move;
                }
            }
        }
    }

    protected function isMyCastleInRangeOfEnemy($pathToMyEmptyCastle, $fields)
    {
        $this->_l->logMethodName();
        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy->x, $enemy->y);
            $h = $mHeuristics->calculateH($pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y);
            if ($h < $enemy->movesLeft) {
                $fields = Application_Model_Board::changeCastleFields($fields, $pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y, 'E');
                try {
                    $aStar = new Cli_Model_Astar($enemy, $pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y, $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                if ($enemy->unitsHaveRange($aStar->getPath($pathToMyEmptyCastle->x . '_' . $pathToMyEmptyCastle->y))) {
                    return true;
                }

                $fields = Application_Model_Board::changeCastleFields($fields, $pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y, 'e');
            }
        }
    }

    protected function canAttackAllEnemyHaveRange($enemiesHaveRange)
    {
        $this->_l->logMethodName();
        foreach ($enemiesHaveRange as $enemy) {
            $castleId = Application_Model_Board::isCastleAtPosition($enemy->x, $enemy->y, $this->_map['hostileCastles']);
            if ($this->isEnemyStronger($enemy, $castleId)) {
                return;
            }
        }
        return $enemy;
    }

    protected function getWeakerEnemyArmyInRange()
    {
        $this->_l->logMethodName();
        $fields = $this->_map['fields'];

        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy->x, $enemy->y);
            $h = $mHeuristics->calculateH($this->_Computer->x, $this->_Computer->y);
            if ($h < $this->_Computer->movesLeft) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy->x, $enemy->y, $this->_map['hostileCastles']);

                if ($this->isEnemyStronger($enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy->x, $enemy->y, 'E');
                } else {
                    $fields = Application_Model_Board::restoreField($fields, $enemy->x, $enemy->y);
                }
                try {
                    $aStar = new Cli_Model_Astar($this->_Computer, $enemy->x, $enemy->y, $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_Computer->calculateMovesSpend($aStar->getPath($enemy->x . '_' . $enemy->y));
                if ($move->x == $enemy->x && $move->y == $enemy->y) {
                    $move->castleId = $castleId;
                    $move->armyId = $enemy->id;
                    return $move;
                }

                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy->x, $enemy->y, 'e');
                } else {
                    $fields = Application_Model_Board::changeArmyField($fields, $enemy->x, $enemy->y, 'e');
                }
            }
        }

        return;
    }

    protected function getStrongerEnemyArmyInRange()
    {
        $this->_l->logMethodName();
        $fields = $this->_map['fields'];

        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy->x, $enemy->y);
            $h = $mHeuristics->calculateH($this->_Computer->x, $this->_Computer->y);
            if ($h < $this->_Computer->movesLeft) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy->x, $enemy->y, $this->_map['hostileCastles']);

                if (!$this->isEnemyStronger($enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy->x, $enemy->y, 'E');
                } else {
                    $fields = Application_Model_Board::restoreField($fields, $enemy->x, $enemy->y);
                }
                try {
                    $aStar = new Cli_Model_Astar($this->_Computer, $enemy->x, $enemy->y, $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_Computer->calculateMovesSpend($aStar->getPath($enemy->x . '_' . $enemy->y));
                if ($move->x == $enemy->x && $move->y == $enemy->y) {
                    return $enemy->id;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy->x, $enemy->y, 'e');
                } else {
                    $fields = Application_Model_Board::changeArmyField($fields, $enemy->x, $enemy->y, 'e');
                }
            }
        }
        return null;
    }

    protected function getPathToMyArmyInRange()
    {
        $this->_l->logMethodName();
        if ($this->_turnNumber < 5) {
            return;
        }

        $numberOfUnits = floor($this->_turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }

        $armyNumber = count($this->_Computer->soldiers) + count($this->_Computer->heroes);

        $myArmies = $this->_mArmyDB->getAllPlayerArmiesExceptOne($this->_Computer->id, $this->_playerId);

        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);

        foreach ($myArmies as $a) {
            $numberOfSoldiers = $mSoldier->count($a['armyId']);

            $castleId = Application_Model_Board::isCastleAtPosition($a['x'], $a['y'], $this->_map['myCastles']);
            if ($castleId) {
                if ($numberOfUnits == $numberOfSoldiers) {
                    continue;
                }
            }

            if ($armyNumber > 3 * $numberOfSoldiers) {
                continue;
            }

            if ($numberOfSoldiers > 3 * $armyNumber) {
                continue;
            }

            $mHeuristics = new Cli_Model_Heuristics($a['x'], $a['y']);
            $h = $mHeuristics->calculateH($this->_Computer->x, $this->_Computer->y);
            if ($h < $this->_Computer->movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_Computer, $a['x'], $a['y'], $this->_map['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_Computer->calculateMovesSpend($aStar->getPath($a['x'] . '_' . $a['y']));
                if ($move->x == $a['x'] && $move->y == $a['y']) {
                    return $move;
                }
            }
        }
        return;
    }

    protected function getMyCastleNearEnemy()
    {
        $this->_l->logMethodName();
        $castlesHeuristics = array();

        foreach ($this->_map['myCastles'] as $j => $castle) {
            $mHeuristics = new Cli_Model_Heuristics($castle['position']['x'], $castle['position']['y']);
            foreach ($this->_enemies as $enemy) {
                if (isset($castlesHeuristics[$j])) {
                    $castlesHeuristics[$j] += $mHeuristics->calculateH($enemy->x, $enemy->y);
                } else {
                    $castlesHeuristics[$j] = $mHeuristics->calculateH($enemy->x, $enemy->y);
                }
            }
        }

        if (empty($castlesHeuristics)) {
            return;
        }

        asort($castlesHeuristics, SORT_NUMERIC);
        reset($castlesHeuristics);
        return $this->_map['myCastles'][key($castlesHeuristics)];
    }

    protected function getPathToMyCastle($castle)
    {
        $this->_l->logMethodName();
        if ($castle['position']['x'] == $this->_Computer->x && $castle['position']['y'] == $this->_Computer->y) {
            return;
        }
        try {
            $aStar = new Cli_Model_Astar($this->_Computer, $castle['position']['x'], $castle['position']['y'], $this->_map['fields']);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $this->_Computer->calculateMovesSpend($aStar->getPath($castle['position']['x'] . '_' . $castle['position']['y']));
    }
}
