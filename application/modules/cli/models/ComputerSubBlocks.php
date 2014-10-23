<?php

class Cli_Model_ComputerSubBlocks extends Cli_Model_ComputerFight
{
    public function getWeakerHostileCastle($castles, $army, $castlesIds = array())
    {
        $heuristics = array();
        foreach ($castles as $id => $castle) {
            if (in_array($id, $castlesIds)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($castle['position']['x'], $castle['position']['y']);
            $heuristics[$id] = $mHeuristics->calculateH($army['x'], $army['y']);
        }

        asort($heuristics, SORT_NUMERIC);

        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

        foreach (array_keys($heuristics) as $id) {
            $position = $castles[$id]['position'];
            if ($mCastlesInGame->isEnemyCastle($id, $this->_playerId)) {
                $enemy = Cli_Model_Army::getCastleGarrisonFromCastlePosition($position, $this->_gameId, $this->_db);
            } else {
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($this->_gameId, $this->_db);
            }
            $enemy = array_merge($enemy, $position);

            if (!$this->isEnemyStronger($army, $enemy, $id)) {
                return $id;
            }
        }
        return null;
    }

    public function isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy)
    {
        $mapCastles = Zend_Registry::get('castles');
        $army = $mArmy->getArmy();
        $position = $mapCastles[$castleId]['position'];
        $fields = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $position['x'], $position['y'], 'E');

        try {
            $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $path = $aStar->getPath($position['x'] . '_' . $position['y']);

        if (empty($path)) {
            return;
        }

        $move = $mArmy->calculateMovesSpend($path);
        if (Application_Model_Board::isCastleField($move['currentPosition'], $position)) {
            $move['in'] = true;
        } else {
            $move['in'] = false;
        }

        $move['fullPath'] = $path;

        return $move;
    }

    public function isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy)
    {
        $army = $mArmy->getArmy();
        $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
        if ($castleId) {
            $castlesAndFields['fields'] = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'E');
        } else {
            $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
        }

        try {
            $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
        if ($castleId) {
            if ($castleId == Application_Model_Board::isCastleAtPosition($move['currentPosition']['x'], $move['currentPosition']['y'], $castlesAndFields['hostileCastles'])) {
                $move['in'] = true;
            } else {
                $move['in'] = false;
            }
        } else {
            if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
                $move['in'] = true;
            } else {
                $move['in'] = false;
            }
        }
        $move['castleId'] = $castleId;
        return $move;
    }

    public function getEnemiesHaveRangeAtThisCastle($castlePosition, $castlesAndFields, $enemies)
    {
        $enemiesHaveRange = array();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($castlePosition['x'], $castlePosition['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < ($enemy['movesLeft'])) {
                $mArmy = new Cli_Model_Army($enemy);
                $enemy = $mArmy->getArmy();

                $castlesAndFields['fields'] = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'E');

                try {
                    $aStar = new Cli_Model_Astar($enemy, $castlePosition['x'], $castlePosition['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $castlesAndFields['fields'] = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'e');

                if ($mArmy->unitsHaveRange($aStar->getPath($castlePosition['x'] . '_' . $castlePosition['y']))) {
                    $enemiesHaveRange[] = $enemy;
                }
            }
        }
        if (!empty($enemiesHaveRange)) {
            return $enemiesHaveRange;
        } else {
            return false;
        }
    }

    public function getEnemiesInRange($enemies, $mArmy, $fields)
    {
        $army = $mArmy->getArmy();
        if (!isset($army['movesLeft'])) {
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
        }
        $enemiesInRange = array();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($army['x'], $army['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < $army['movesLeft']) {
                $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $fields = Application_Model_Board::changeArmyField($fields, $enemy['x'], $enemy['y'], 'e');

                $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
                    $enemiesInRange[] = $enemy;
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

    public function getNearestRuin($fields, $ruins, $mArmy)
    {
        $army = $mArmy->getArmy();
        if (!isset($army['movesLeft'])) {
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
        }
        foreach ($ruins as $ruinId => $ruin) {
            $mHeuristics = new Cli_Model_Heuristics($ruin['x'], $ruin['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($army, $ruin['x'], $ruin['y'], $fields, array('limit' => true));
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($ruin['x'] . '_' . $ruin['y']));
                if ($move['currentPosition']['x'] == $ruin['x'] && $move['currentPosition']['y'] == $ruin['y']) {
                    $ruin['ruinId'] = $ruinId;
                    return array_merge($ruin, $move);
                }
            }
        }
    }

    public function getMyEmptyCastleInMyRange($mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();

        foreach ($castlesAndFields['myCastles'] as $castle) {
            $position = $castle['position'];
            if ($this->_modelArmy->areUnitsAtCastlePosition($position)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($army['x'], $army['y']);
            $h = $mHeuristics->calculateH($position['x'], $position['y']);
            if ($h < $army['movesLeft']) {
//                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'E');
                try {
                    $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
                if ($move['currentPosition']['x'] == $position['x'] && $move['currentPosition']['y'] == $position['y']) {
//                    $castle['movesSpend'] = $movesToSpend;
//                    $castle['path'] = $aStar->getPath($key, $army['movesLeft']);
//                    $castle['currentPosition'] = $aStar->getCurrentPosition();
                    $castle['x'] = $position['x'];
                    $castle['y'] = $position['y'];
                    return array_merge($castle, $move);
                }

//                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'e');
            }
        }
    }

    public function isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $fields)
    {
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($myEmptyCastle['x'], $myEmptyCastle['y']);
            if ($h < $enemy['movesLeft']) {
                $fields = Application_Model_Board::changeCastleFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'E');
                $mArmy = new Cli_Model_Army($enemy);
                $enemy = $mArmy->getArmy();
                try {
                    $aStar = new Cli_Model_Astar($enemy, $myEmptyCastle['x'], $myEmptyCastle['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                if ($mArmy->unitsHaveRange($aStar->getPath($myEmptyCastle['x'] . '_' . $myEmptyCastle['y']))) {
                    return true;
                }

                $fields = Application_Model_Board::changeCastleFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'e');
            }
        }
    }

    public function canAttackAllEnemyHaveRange($enemies, $army, $castles)
    {
        foreach ($enemies as $enemy) {
            $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castles);
            $enemy['castleId'] = $castleId;
            if ($this->isEnemyStronger($army, $enemy, $castleId)) {
                return;
            }
        }
        return $enemy;
    }

    public function getWeakerEnemyArmyInRange($enemies, $mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();
        if (!isset($army['movesLeft'])) {
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
        }
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if ($this->isEnemyStronger($army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'E');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
                    $enemy['castleId'] = $castleId;
                    return array_merge($enemy, $move);
                }

                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::changeArmyField($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                }
            }
        }
        return null;
    }

    public function getStrongerEnemyArmyInRange($enemies, $mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();
        if (!isset($army['movesLeft'])) {
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
        }
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if (!$this->isEnemyStronger($army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'E');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
//                    array_merge($enemy, $move);
//                    $enemy['castleId'] = $castleId;
                    return true;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCastleFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::changeArmyField($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                }
            }
        }
        return null;
    }

    public function getMyArmyInRange($mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();
        if (!isset($army['movesLeft'])) {
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
        }

        if ($this->_turnNumber < 5) {
            return;
        }

        $numberOfUnits = floor($this->_turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }

        $armyNumber = count($army['soldiers']) + count($army['heroes']);

        $myArmies = $this->_modelArmy->getAllPlayerArmiesExceptOne($army['armyId'], $this->_playerId);

        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);

        foreach ($myArmies as $a) {
            $numberOfSoldiers = $mSoldier->count($a['armyId']);

            $castleId = Application_Model_Board::isCastleAtPosition($a['x'], $a['y'], $castlesAndFields['myCastles']);
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
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($army, $a['x'], $a['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($a['x'] . '_' . $a['y']));
                if ($move['currentPosition']['x'] == $a['x'] && $move['currentPosition']['y'] == $a['y']) {
                    return array_merge($a, $move);
                }
            }
        }
        return;
    }

    public function getMyCastleNearEnemy($enemies, $mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();
        $heuristics = array();

        foreach ($enemies as $k => $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $heuristics[$k] = $mHeuristics->calculateH($army['x'], $army['y']);
        }

        if (empty($heuristics)) {
            return;
        }

        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $heuristics = array();

        foreach ($castlesAndFields['myCastles'] as $j => $castle) {
            $position = $castle['castleId']['position'];
            $mHeuristics = new Cli_Model_Heuristics($enemies[$k]['x'], $enemies[$k]['y']);
            $heuristics[$j] = $mHeuristics->calculateH($position['x'], $position['y']);
        }

        if (empty($heuristics)) {
            return;
        }

        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $castle = $castlesAndFields['myCastles'][$k];

        try {
            $aStar = new Cli_Model_Astar($army, $castle['position']['x'], $castle['position']['y'], $castlesAndFields['fields']);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $mArmy->calculateMovesSpend($aStar->getPath($castle['position']['x'] . '_' . $castle['position']['y']));
        if ($move['currentPosition'] == $position) {
            return array_merge($castle, $move);
        } else {
            return null;
        }
    }

}

