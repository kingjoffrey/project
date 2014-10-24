<?php

class Cli_Model_ComputerSubBlocks extends Cli_Model_ComputerFight
{
    public function getWeakerHostileCastle($castles, $castlesIds = array())
    {
        $heuristics = array();
        foreach ($castles as $id => $castle) {
            if (in_array($id, $castlesIds)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($castle['position']['x'], $castle['position']['y']);
            $heuristics[$id] = $mHeuristics->calculateH($this->_army['x'], $this->_army['y']);
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

            if (!$this->isEnemyStronger($enemy, $id)) {
                return $id;
            }
        }
        return null;
    }

    public function isEnemyCastleInRange($castleId)
    {
        $mapCastles = Zend_Registry::get('castles');
        $position = $mapCastles[$castleId]['position'];
        $fields = Application_Model_Board::changeCastleFields($this->_map['fields'], $position['x'], $position['y'], 'E');

        try {
            $aStar = new Cli_Model_Astar($this->_army, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $path = $aStar->getPath($position['x'] . '_' . $position['y']);

        if (empty($path)) {
            return;
        }

        $move = $this->_mArmy->calculateMovesSpend($path);
        if (Application_Model_Board::isCastleField($move['currentPosition'], $position)) {
            $move['in'] = true;
        } else {
            $move['in'] = false;
        }

        return $move;
    }

    public function isEnemyArmyInRange($enemy)
    {
        $fields = $this->_map['fields'];

        $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $this->_map['hostileCastles']);
        if ($castleId) {
            $fields = Application_Model_Board::changeCastleFields($fields, $enemy['x'], $enemy['y'], 'E');
        } else {
            $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
        }

        try {
            $aStar = new Cli_Model_Astar($this->_army, $enemy['x'], $enemy['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
        if ($castleId) {
            if ($castleId == Application_Model_Board::isCastleAtPosition($move['currentPosition']['x'], $move['currentPosition']['y'], $this->_map['hostileCastles'])) {
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

    public function getEnemiesHaveRangeAtThisCastle($castlePosition)
    {
        $enemiesHaveRange = array();
        $fields = $this->_map['fields'];

        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($castlePosition['x'], $castlePosition['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < ($enemy['movesLeft'])) {
                $mArmy = new Cli_Model_Army($enemy);
                $enemy = $mArmy->getArmy();

                $fields = Application_Model_Board::changeCastleFields($fields, $castlePosition['x'], $castlePosition['y'], 'E');

                try {
                    $aStar = new Cli_Model_Astar($enemy, $castlePosition['x'], $castlePosition['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $fields = Application_Model_Board::changeCastleFields($fields, $castlePosition['x'], $castlePosition['y'], 'e');

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

    public function getEnemiesInRange($fields)
    {
        if (!isset($this->_army['movesLeft'])) {
            $this->_army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($this->_army);
        }
        $enemiesInRange = array();
        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($this->_army['x'], $this->_army['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < $this->_army['movesLeft']) {
                $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $enemy['x'], $enemy['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $fields = Application_Model_Board::changeArmyField($fields, $enemy['x'], $enemy['y'], 'e');

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
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

    public function getNearestRuin($fields, $ruins)
    {
        if (!isset($this->_army['movesLeft'])) {
            $this->_army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($this->_army);
        }
        foreach ($ruins as $ruinId => $ruin) {
            $mHeuristics = new Cli_Model_Heuristics($ruin['x'], $ruin['y']);
            $h = $mHeuristics->calculateH($this->_army['x'], $this->_army['y']);
            if ($h < $this->_army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $ruin['x'], $ruin['y'], $fields, array('limit' => true));
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($ruin['x'] . '_' . $ruin['y']));
                if ($move['currentPosition']['x'] == $ruin['x'] && $move['currentPosition']['y'] == $ruin['y']) {
                    $ruin['ruinId'] = $ruinId;
                    return array_merge($ruin, $move);
                }
            }
        }
    }

    public function getMyEmptyCastleInMyRange()
    {
        foreach ($this->_map['myCastles'] as $castle) {
            $position = $castle['position'];
            if ($this->_mArmyDB->areUnitsAtCastlePosition($position)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($this->_army['x'], $this->_army['y']);
            $h = $mHeuristics->calculateH($position['x'], $position['y']);
            if ($h < $this->_army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $position['x'], $position['y'], $this->_map['fields']);
                } catch (Exception $e) {
                    $this->_l->log($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
                if ($move['currentPosition']['x'] == $position['x'] && $move['currentPosition']['y'] == $position['y']) {
                    $castle['x'] = $position['x'];
                    $castle['y'] = $position['y'];
                    return array_merge($castle, $move);
                }
            }
        }
    }

    public function isMyCastleInRangeOfEnemy($myEmptyCastle, $fields)
    {
        foreach ($this->_enemies as $enemy) {
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

    public function canAttackAllEnemyHaveRange()
    {
        foreach ($this->_enemies as $enemy) {
            $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $this->_map['hostileCastles']);
            $enemy['castleId'] = $castleId;
            if ($this->isEnemyStronger($enemy, $castleId)) {
                return;
            }
        }
        return $enemy;
    }

    public function getWeakerEnemyArmyInRange()
    {
        $fields = $this->_map['fields'];

        if (!isset($this->_army['movesLeft'])) {
            $this->_army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($this->_army);
        }

        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($this->_army['x'], $this->_army['y']);
            if ($h < $this->_army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $this->_map['hostileCastles']);
                if ($this->isEnemyStronger($enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy['x'], $enemy['y'], 'E');
                } else {
                    $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $enemy['x'], $enemy['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
                    $enemy['castleId'] = $castleId;
                    return array_merge($enemy, $move);
                }

                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy['x'], $enemy['y'], 'e');
                } else {
                    $fields = Application_Model_Board::changeArmyField($fields, $enemy['x'], $enemy['y'], 'e');
                }
            }
        }
        return null;
    }

    public function getStrongerEnemyArmyInRange()
    {
        $fields = $this->_map['fields'];

        if (!isset($this->_army['movesLeft'])) {
            $this->_army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($this->_army);
        }
        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($this->_army['x'], $this->_army['y']);
            if ($h < $this->_army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $this->_map['hostileCastles']);
                if (!$this->isEnemyStronger($enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy['x'], $enemy['y'], 'E');
                } else {
                    $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $enemy['x'], $enemy['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
//                    array_merge($enemy, $move);
//                    $enemy['castleId'] = $castleId;
                    return true;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy['x'], $enemy['y'], 'e');
                } else {
                    $fields = Application_Model_Board::changeArmyField($fields, $enemy['x'], $enemy['y'], 'e');
                }
            }
        }
        return null;
    }

    public function getMyArmyInRange()
    {
        if (!isset($this->_army['movesLeft'])) {
            $this->_army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($this->_army);
        }

        if ($this->_turnNumber < 5) {
            return;
        }

        $numberOfUnits = floor($this->_turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }

        $armyNumber = count($this->_army['soldiers']) + count($this->_army['heroes']);

        $myArmies = $this->_mArmyDB->getAllPlayerArmiesExceptOne($this->_army['armyId'], $this->_playerId);

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
            $h = $mHeuristics->calculateH($this->_army['x'], $this->_army['y']);
            if ($h < $this->_army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($this->_army, $a['x'], $a['y'], $this->_map['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($a['x'] . '_' . $a['y']));
                if ($move['currentPosition']['x'] == $a['x'] && $move['currentPosition']['y'] == $a['y']) {
                    return array_merge($a, $move);
                }
            }
        }
        return;
    }

    public function getMyCastleNearEnemy()
    {
        $heuristics = array();

        foreach ($this->_enemies as $k => $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $heuristics[$k] = $mHeuristics->calculateH($this->_army['x'], $this->_army['y']);
        }

        if (empty($heuristics)) {
            return;
        }

        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $heuristics = array();

        foreach ($this->_map['myCastles'] as $j => $castle) {
            $position = $castle['castleId']['position'];
            $mHeuristics = new Cli_Model_Heuristics($this->_enemies[$k]['x'], $this->_enemies[$k]['y']);
            $heuristics[$j] = $mHeuristics->calculateH($position['x'], $position['y']);
        }

        if (empty($heuristics)) {
            return;
        }

        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $castle = $this->_map['myCastles'][$k];

        try {
            $aStar = new Cli_Model_Astar($this->_army, $castle['position']['x'], $castle['position']['y'], $this->_map['fields']);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($castle['position']['x'] . '_' . $castle['position']['y']));

        if ($move['currentPosition'] == $position) {
            return array_merge($castle, $move);
        } else {
            return null;
        }
    }

    protected function findNearestWeakestHostileCastle()
    {
        $omittedCastlesIds = array();
        $weakerHostileCastleId = $this->getWeakerHostileCastle($this->_map['hostileCastles'], $this->_army);

        if ($weakerHostileCastleId) {
            $castleRange = $this->isEnemyCastleInRange($weakerHostileCastleId);
            while (true) {
                if (empty($castleRange)) {
                    $omittedCastlesIds[] = $weakerHostileCastleId;
                    $weakerHostileCastleId = $this->getWeakerHostileCastle($this->_map['hostileCastles'], $this->_army, $omittedCastlesIds);
                    if ($weakerHostileCastleId) {
                        $castleRange = $this->isEnemyCastleInRange($weakerHostileCastleId);
                    } else {
                        break;
                    }
                }
                break;
            }
        }

        $castleRange['weakerHostileCastleId'] = $weakerHostileCastleId;

        return $castleRange;
    }

    protected function initMap()
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);

        $this->_map = Application_Model_Board::prepareCastlesAndFields(
            Cli_Model_Army::getEnemyArmiesFieldsPositions($this->_gameId, $this->_db, $this->_playerId),
            $mCastlesInGame->getRazedCastles(),
            $mCastlesInGame->getPlayerCastles($this->_playerId),
            $mCastlesInGame->getTeamCastles($this->_playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($this->_playerId))
        );

//        var_dump($this->_map['hostileCastles']);exit;
    }
}

