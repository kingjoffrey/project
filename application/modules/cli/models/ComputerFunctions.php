<?php

class Cli_Model_ComputerFunctions extends Cli_Model_ComputerFight
{
    protected $_gameId;
    protected $_playerId;
    protected $_db;
    protected $_mArmyDB;
    protected $_mArmy;
    protected $_gameHandler;
    protected $_l;
    protected $_mGame;
    protected $_turnNumber;
    protected $_map;
    protected $_enemies;

    public function __construct($gameId, $playerId, $db)
    {
        $this->_gameId = $gameId;
        $this->_playerId = $playerId;
        $this->_db = $db;
        $this->_mArmyDB = new Application_Model_Army($this->_gameId, $this->_db);
    }

    public function getWeakerHostileCastle($castles, $castlesIds = array())
    {
        $heuristics = array();
        foreach ($castles as $id => $castle) {
            if (in_array($id, $castlesIds)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($castle['position']['x'], $castle['position']['y']);
            $heuristics[$id] = $mHeuristics->calculateH($this->_mArmy->x, $this->mArmy->y);
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

            if (!$this->isEnemyStronger(new Cli_Model_Army($enemy), $id)) {
                return $id;
            }
        }
        return null;
    }

    public function getPathToEnemyCastleInRange($castleId)
    {
        $mapCastles = Zend_Registry::get('castles');
        $position = $mapCastles[$castleId]['position'];
        $fields = Application_Model_Board::changeCastleFields($this->_map['fields'], $position['x'], $position['y'], 'E');

        try {
            $aStar = new Cli_Model_Astar($this->_mArmy, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $path = $aStar->getPath($position['x'] . '_' . $position['y']);

        if (empty($path)) {
            return;
        }

        $move = $this->_mArmy->calculateMovesSpend($path);
        if (Application_Model_Board::isCastleField($move->end, $position)) {
            $move->in = true;
        } else {
            $move->in = false;
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
            $aStar = new Cli_Model_Astar($this->_mArmy, $enemy['x'], $enemy['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
        if ($castleId) {
            if ($castleId == Application_Model_Board::isCastleAtPosition($move->x, $move->y, $this->_map['hostileCastles'])) {
                $move->castleId = $castleId;
                return $move;
            }
        } else {
            if ($move->x == $enemy['x'] && $move->y == $enemy['y']) {
                $move->castleId = null;
                return $move;
            }
        }
    }

    public function getEnemiesHaveRangeAtThisCastle($castlePosition)
    {
        $enemiesHaveRange = array();
        $fields = $this->_map['fields'];

        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($castlePosition['x'], $castlePosition['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < ($enemy['movesLeft'])) {
                $mEnemy = new Cli_Model_Army($enemy);

                $fields = Application_Model_Board::changeCastleFields($fields, $castlePosition['x'], $castlePosition['y'], 'E');

                try {
                    $aStar = new Cli_Model_Astar($mEnemy, $castlePosition['x'], $castlePosition['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $fields = Application_Model_Board::changeCastleFields($fields, $castlePosition['x'], $castlePosition['y'], 'e');

                if ($mEnemy->unitsHaveRange($aStar->getPath($castlePosition['x'] . '_' . $castlePosition['y']))) {
                    $enemiesHaveRange[] = $mEnemy;
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
        $enemiesInRange = array();
        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($this->_mArmy->x, $this->_mArmy->y);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < $this->_mArmy->movesLeft) {
                $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                try {
                    $aStar = new Cli_Model_Astar($this->_mArmy, $enemy['x'], $enemy['y'], $fields);
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

    public function getPathToNearestRuin($fields, $ruins)
    {
        foreach ($ruins as $ruinId => $ruin) {
            $mHeuristics = new Cli_Model_Heuristics($ruin['x'], $ruin['y']);
            $h = $mHeuristics->calculateH($this->_mArmy->x, $this->_mArmy->y);
            if ($h < $this->_mArmy->movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_mArmy, $ruin['x'], $ruin['y'], $fields, array('limit' => true));
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $path = $this->_mArmy->calculateMovesSpend($aStar->getPath($ruin['x'] . '_' . $ruin['y']));
                if ($path->x == $ruin['x'] && $path->y == $ruin['y']) {
                    $path->ruinId = $ruinId;
                    return $path;
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
            $mHeuristics = new Cli_Model_Heuristics($this->_mArmy->x, $this->_mArmy->y);
            $h = $mHeuristics->calculateH($position['x'], $position['y']);
            if ($h < $this->_mArmy->movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_mArmy, $position['x'], $position['y'], $this->_map['fields']);
                } catch (Exception $e) {
                    $this->_l->log($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
                if ($move->x == $position['x'] && $move->y == $position['y']) {
                    return $move;
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
                $mEnemy = new Cli_Model_Army($enemy);
                try {
                    $aStar = new Cli_Model_Astar($mEnemy, $myEmptyCastle['x'], $myEmptyCastle['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                if ($mEnemy->unitsHaveRange($aStar->getPath($myEmptyCastle['x'] . '_' . $myEmptyCastle['y']))) {
                    return true;
                }

                $fields = Application_Model_Board::changeCastleFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'e');
            }
        }
    }

    public function canAttackAllEnemyHaveRange($enemiesHaveRange)
    {
        foreach ($enemiesHaveRange as $enemy) {
            $castleId = Application_Model_Board::isCastleAtPosition($enemy->x, $enemy->y, $this->_map['hostileCastles']);
            $enemy['castleId'] = $castleId;
            if ($this->isEnemyStronger(new Cli_Model_Army($enemy), $castleId)) {
                return;
            }
        }
        return $enemy;
    }

    public function getWeakerEnemyArmyInRange()
    {
        $fields = $this->_map['fields'];

        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($this->_mArmy->x, $this->_mArmy->y);
            if ($h < $this->_mArmy->movesLeft) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $this->_map['hostileCastles']);

                if ($this->isEnemyStronger(new Cli_Model_Army($enemy), $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy['x'], $enemy['y'], 'E');
                } else {
                    $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($this->_mArmy, $enemy['x'], $enemy['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move->x == $enemy['x'] && $move->y == $enemy['y']) {
                    $move->castleId = $castleId;
                    $move->armyId = $enemy['armyId'];
                    return $move;
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

        foreach ($this->_enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($this->_mArmy->x, $this->_mArmy->y);
            if ($h < $this->_mArmy->movesLeft) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $this->_map['hostileCastles']);

                if (!$this->isEnemyStronger(new Cli_Model_Army($enemy), $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $fields = Application_Model_Board::changeCastleFields($fields, $enemy['x'], $enemy['y'], 'E');
                } else {
                    $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($this->_mArmy, $enemy['x'], $enemy['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move->x == $enemy['x'] && $move->y == $enemy['y']) {
                    return $enemy['armyId'];
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

    public function getPathToMyArmyInRange()
    {
        if ($this->_turnNumber < 5) {
            return;
        }

        $numberOfUnits = floor($this->_turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }

        $armyNumber = count($this->_mArmy->soldiers) + count($this->_mArmy->heroes);

        $myArmies = $this->_mArmyDB->getAllPlayerArmiesExceptOne($this->_mArmy->id, $this->_playerId);

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
            $h = $mHeuristics->calculateH($this->_mArmy->x, $this->_mArmy->y);
            if ($h < $this->_mArmy->movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($this->_mArmy, $a['x'], $a['y'], $this->_map['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $this->_mArmy->calculateMovesSpend($aStar->getPath($a['x'] . '_' . $a['y']));
                if ($move->x == $a['x'] && $move->y == $a['y']) {
                    return $move;
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
            $heuristics[$k] = $mHeuristics->calculateH($this->_mArmy->x, $this->_mArmy->y);
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
            $aStar = new Cli_Model_Astar($this->_mArmy, $castle['position']['x'], $castle['position']['y'], $this->_map['fields']);
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
        $weakerHostileCastleId = $this->getWeakerHostileCastle($this->_map['hostileCastles']);

        if ($weakerHostileCastleId) {
            $path = $this->getPathToEnemyCastleInRange($weakerHostileCastleId);
            while (true) {
                if (empty($path)) {
                    $omittedCastlesIds[] = $weakerHostileCastleId;
                    $weakerHostileCastleId = $this->getWeakerHostileCastle($this->_map['hostileCastles'], $omittedCastlesIds);
                    if ($weakerHostileCastleId) {
                        $path = $this->getPathToEnemyCastleInRange($weakerHostileCastleId);
                    } else {
                        break;
                    }
                }
                break;
            }
        }

        $path->castleId = $weakerHostileCastleId;

        return $path;
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
    }
}

