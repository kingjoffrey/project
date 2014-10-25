<?php

class Cli_Model_Army
{

    private $units;

    private $terrain;

    public $id;
    public $x;
    public $y;
    public $defenseModifier = 0;
    public $attackModifier = 0;

    private $canFly = 0;
    private $canSwim = 0;

    public $heroes = array();
    public $soldiers = array();

    public $movesLeft = 0;

    public function __construct($army)
    {
        $this->units = Zend_Registry::get('units');
        $this->terrain = Zend_Registry::get('terrain');

        $this->id = $army['armyId'];
        $this->x = $army['x'];
        $this->y = $army['y'];

        $this->movesLeft = Cli_Model_Army::calculateMaxArmyMoves($army);

        $this->heroes = $army['heroes'];
        $this->soldiers = $army['soldiers'];

        $numberOfHeroes = count($this->heroes);
        if ($numberOfHeroes) {
            $this->attackModifier++;
            $modMovesForest = 3;
            $modMovesSwamp = 4;
            $modMovesHills = 5;
        } else {
            $modMovesForest = 0;
            $modMovesSwamp = 0;
            $modMovesHills = 0;
        }
        $this->canFly = -$numberOfHeroes + 1;
        $this->canSwim = 0;

        $attackFlyModifier = 0;

        foreach ($this->soldiers as $soldier) {
            $unit = $this->units[$soldier['unitId']];

            if ($unit['modMovesForest'] > $modMovesForest) {
                $modMovesForest = $unit['modMovesForest'];
            }
            if ($unit['modMovesSwamp'] > $modMovesSwamp) {
                $modMovesSwamp = $unit['modMovesSwamp'];
            }
            if ($unit['modMovesHills'] > $modMovesHills) {
                $modMovesHills = $unit['modMovesHills'];
            }

            if ($unit['canFly']) {
                $attackFlyModifier++;
                $this->canFly++;
            } else {
                $this->canFly -= 200;
            }
            if ($unit['canSwim']) {
                $this->canSwim++;
            }
        }

        if ($attackFlyModifier) {
            $this->attackModifier++;
        }
    }

    public function getArmyId()
    {
        return $this->id;
    }

    public function calculateMovesSpend($fullPath)
    {
        if ($this->canFly()) {
            $currentPath = $this->calculateMovesSpendFlying($fullPath);
        } elseif ($this->canSwim()) {
            $currentPath = $this->calculateMovesSpendSwimming($fullPath);
        } else {
            $currentPath = $this->calculateMovesSpendWalking($fullPath);
        }

        return new Cli_Model_Path($currentPath, $fullPath);
//        return array(
//            'path' => $currentPath,
//            'fullPath' => $fullPath,
//            'currentPosition' => end($currentPath)
//        );
    }

    private function calculateMovesSpendFlying($fullPath)
    {
        $currentPath = array();

        foreach ($this->soldiers as $soldier) {
            if (!$this->units[$soldier['unitId']]['canFly']) {
                continue;
            }

            if (!isset($movesLeft)) {
                $movesLeft = $soldier['movesLeft'];
                continue;
            }

            if ($movesLeft > $soldier['movesLeft']) {
                $movesLeft = $soldier['movesLeft'];
            }
        }

        for ($i = 0; $i < count($fullPath); $i++) {
            if (!isset($fullPath[$i]['cc'])) {
                $movesLeft -= $this->terrain[$fullPath[$i]['tt']]['flying'];
            }

            if ($movesLeft < 0) {
                break;
            }

            if (isset($fullPath[$i]['cc'])) {
                $currentPath[] = array(
                    'x' => $fullPath[$i]['x'],
                    'y' => $fullPath[$i]['y'],
                    'tt' => $fullPath[$i]['tt'],
                    'myCastleCosts' => true
                );
            } else {
                $currentPath[] = array(
                    'x' => $fullPath[$i]['x'],
                    'y' => $fullPath[$i]['y'],
                    'tt' => $fullPath[$i]['tt']
                );
            }

            if ($fullPath[$i]['tt'] == 'E') {
                break;
            }

            if ($movesLeft == 0) {
                break;
            }
        }

        return $currentPath;
    }

    private function calculateMovesSpendSwimming($fullPath)
    {
        $currentPath = array();

        foreach ($this->soldiers as $soldier) {
            if (!$this->units[$soldier['unitId']]['canSwim']) {
                continue;
            }

            if (!isset($movesLeft)) {
                $movesLeft = $soldier['movesLeft'];
                continue;
            }

            if ($movesLeft > $soldier['movesLeft']) {
                $movesLeft = $soldier['movesLeft'];
            }
        }


        for ($i = 0; $i < count($fullPath); $i++) {
            if (!isset($fullPath[$i]['cc'])) {
                $movesLeft -= $this->terrain[$fullPath[$i]['tt']]['swimming'];
            }

            if ($movesLeft < 0) {
                break;
            }

            if (isset($fullPath[$i]['cc'])) {
                $currentPath[] = array(
                    'x' => $fullPath[$i]['x'],
                    'y' => $fullPath[$i]['y'],
                    'tt' => $fullPath[$i]['tt'],
                    'myCastleCosts' => true
                );
            } else {
                $currentPath[] = array(
                    'x' => $fullPath[$i]['x'],
                    'y' => $fullPath[$i]['y'],
                    'tt' => $fullPath[$i]['tt']
                );
            }

            if ($fullPath[$i]['tt'] == 'E') {
                break;
            }

            if ($movesLeft == 0) {
                break;
            }
        }

        return $currentPath;
    }

    private function calculateMovesSpendWalking($fullPath)
    {
        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();
        $currentPath = array();
        $stop = false;
        $skip = false;

        for ($i = 0; $i < count($fullPath); $i++) {
            $defaultMoveCost = $this->terrain[$fullPath[$i]['tt']]['walking'];

            foreach ($this->soldiers as $soldier) {
                if (!isset($soldiersMovesLeft[$soldier['soldierId']])) {
                    $soldiersMovesLeft[$soldier['soldierId']] = $soldier['movesLeft'];
                }

                if ($fullPath[$i]['tt'] == 'f') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesForest'];
                } elseif ($fullPath[$i]['tt'] == 's') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($fullPath[$i]['tt'] == 'm') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesHills'];
                } elseif (!isset($fullPath[$i]['cc'])) {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $defaultMoveCost;
                }

                if ($soldiersMovesLeft[$soldier['soldierId']] < 0) {
                    $skip = true;
                }

                if ($soldiersMovesLeft[$soldier['soldierId']] <= 0) {
                    $stop = true;
                    break;
                }
            }

            foreach ($this->heroes as $hero) {
                if (!isset($heroesMovesLeft[$hero['heroId']])) {
                    $heroesMovesLeft[$hero['heroId']] = $hero['movesLeft'];
                }

                if (!isset($fullPath[$i]['cc'])) {
                    $heroesMovesLeft[$hero['heroId']] -= $defaultMoveCost;
                }

                if ($heroesMovesLeft[$hero['heroId']] < 0) {
                    $skip = true;
                }

                if ($heroesMovesLeft[$hero['heroId']] <= 0) {
                    $stop = true;
                    break;
                }
            }

            if ($skip) {
                break;
            }

            if (isset($fullPath[$i]['cc'])) {
                $currentPath[] = array(
                    'x' => $fullPath[$i]['x'],
                    'y' => $fullPath[$i]['y'],
                    'tt' => $fullPath[$i]['tt'],
                    'myCastleCosts' => true
                );
            } else {
                $currentPath[] = array(
                    'x' => $fullPath[$i]['x'],
                    'y' => $fullPath[$i]['y'],
                    'tt' => $fullPath[$i]['tt']
                );
            }

            if ($fullPath[$i]['tt'] == 'E') {
                break;
            }

            if ($stop) {
                break;
            }
        }

        return $currentPath;
    }

    static public function setCombatDefenseModifiers($army)
    {
        if (!isset($army['defenseModifier'])) {
            $army['defenseModifier'] = 0;
        }

        if ($army['heroes']) {
            $army['defenseModifier']++;
        }

        if (isset($army['canFly']) && $army['canFly']) {
            $army['defenseModifier']++;
        }

        return $army;
    }

    static public function addTowerDefenseModifier($army)
    {
        if (!isset($army['x'])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            exit;
        }
        if (Application_Model_Board::isTowerAtPosition($army['x'], $army['y'])) {
            if (isset($army['defenseModifier'])) {
                $army['defenseModifier']++;
            } else {
                $army['defenseModifier'] = 1;
            }
        }
        return $army;
    }

    static public function addCastleDefenseModifier($army, $gameId, $castleId, $db)
    {
        $mapCastles = Zend_Registry::get('castles');

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $defenseModifier = $mapCastles[$castleId]['defense'] + $mCastlesInGame->getCastleDefenseModifier($castleId);

        if ($defenseModifier > 0) {
            if (isset($army['defenseModifier'])) {
                $army['defenseModifier'] += $defenseModifier;
            } else {
                $army['defenseModifier'] = $defenseModifier;
            }
        } else {
            throw new Exception('$defenseModifier <= 0');
            echo 'error! !';
        }
        return $army;
    }

    static public function armyArray($columnName = '')
    {
        return array('armyId', 'destroyed', 'fortified', 'x', 'y', $columnName);
    }

    public function canSwim()
    {
        if ($this->canSwim) {
            return true;
        }
    }

    public function canFly()
    {
        if ($this->canFly > 0) {
            return true;
        }
    }

    public function unitsHaveRange($fullPath)
    {
        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();

        for ($i = 0; $i < count($fullPath); $i++) {
            $defaultMoveCost = $this->terrain[$fullPath[$i]['tt']]['walking'];

            foreach ($this->soldiers as $soldier) {
                if (!isset($soldiersMovesLeft[$soldier['soldierId']])) {
                    $soldiersMovesLeft[$soldier['soldierId']] = $this->units[$soldier['unitId']]['numberOfMoves'];
                    if ($soldier['movesLeft'] <= 2) {
                        $soldiersMovesLeft[$soldier['soldierId']] += $soldier['movesLeft'];
                    } elseif ($soldier['movesLeft'] > 2) {
                        $soldiersMovesLeft[$soldier['soldierId']] += 2;
                    }
                }

                if ($this->canFly()) {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $defaultMoveCost;
                } else {
                    if ($fullPath[$i]['tt'] == 'f') {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesForest'];
                    } elseif ($fullPath[$i]['tt'] == 's') {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesSwamp'];
                    } elseif ($fullPath[$i]['tt'] == 'm') {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesHills'];
                    } else {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $defaultMoveCost;
                    }
                }
            }

            foreach ($this->heroes as $hero) {
                if (!isset($heroesMovesLeft[$hero['heroId']])) {
                    $heroesMovesLeft[$hero['heroId']] = $hero['numberOfMoves'];
                    if ($hero['movesLeft'] <= 2) {
                        $heroesMovesLeft[$hero['heroId']] += $hero['movesLeft'];
                    } elseif ($hero['movesLeft'] > 2) {
                        $heroesMovesLeft[$hero['heroId']] += 2;
                    }
                }

                $heroesMovesLeft[$hero['heroId']] -= $defaultMoveCost;
            }

            if ($fullPath[$i]['tt'] == 'E') {
                break;
            }
        }

        foreach ($soldiersMovesLeft as $s) {
            if ($s >= 0) {
                return true;
            }
        }

        foreach ($heroesMovesLeft as $h) {
            if ($h >= 0) {
                return true;
            }
        }
    }

    static public function calculateMaxArmyMoves($army)
    {
        foreach ($army['heroes'] as $hero) {
            if (!isset($heroMoves)) {
                $heroMoves = $hero['movesLeft'];
            }

            if ($hero['movesLeft'] < $heroMoves) {
                $heroMoves = $hero['movesLeft'];
            }
        }

        foreach ($army['soldiers'] as $soldier) {
            if (!isset($soldierMoves)) {
                $soldierMoves = $soldier['movesLeft'];
            }

            if ($soldier['movesLeft'] < $soldierMoves) {
                $soldierMoves = $soldier['movesLeft'];
            }
        }

        if (!isset($heroMoves)) {
            $heroMoves = 0;
        }

        if (!isset($soldierMoves)) {
            $soldierMoves = 0;
        }

        if ($heroMoves > $soldierMoves) {
            return $heroMoves;
        } else {
            return $soldierMoves;
        }
    }

    static public function heroResurrection($gameId, $heroId, $position, $playerId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armyId = $mArmy->getArmyIdFromPosition($position);
        if (!$armyId) {
            $armyId = $mArmy->createArmy($position, $playerId);
        }
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->addToArmy($armyId, $heroId, 0);

        return $armyId;
    }

    static public function countUnitValue($unit, $productionTime)
    {
        return ($unit['attackPoints'] + $unit['defensePoints'] + $unit['canFly']) / ($productionTime + $unit['cost']);
    }

    static public function findBestCastleProduction(array $units, array $production)
    {
        $value = 0;
        $bestUnitId = null;

        foreach ($production as $unitId => $row) {

            $tmpValue = self::countUnitValue($units[$unitId], $row['time']);

            if ($tmpValue > $value) {
                $value = $tmpValue;
                $bestUnitId = $unitId;
            }
        }

        return $bestUnitId;
    }

    static public function getCastleGarrisonFromCastlePosition($castlePosition, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $ids = $mArmy->getCastleGarrisonFromCastlePosition($castlePosition);

        if ($ids) {
            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            return array(
                'heroes' => $mHeroesInGame->getForBattle($ids),
                'soldiers' => $mSoldier->getForBattle($ids),
                'ids' => $ids
            );
        } else {
            return array(
                'heroes' => array(),
                'soldiers' => array(),
                'ids' => array()
            );
        }

    }

    static public function getArmiesFromCastlePosition($castlePosition, $gameId, $playerId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armies = $mArmy->getArmiesFromCastlePosition($castlePosition, $playerId);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($armies as $k => $army) {
            $armies[$k]['heroes'] = $mHeroesInGame->getForMove($army['armyId']);
            $armies[$k]['soldiers'] = $mSoldier->getForMove($army['armyId']);
        }

        return $armies;
    }

    static public function isCastleGarrisonSufficient($expectedNumberOfUnits, $armiesInCastle)
    {
        foreach ($armiesInCastle as $army) {
            if (count($army['soldiers']) == $expectedNumberOfUnits && count($army['heroes']) == 0) {
                return $army['armyId'];
            }
        }
    }

    static public function getArmyByArmyIdPlayerId($armyId, $playerId, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $result = $mArmy->getArmyByArmyIdPlayerId($armyId, $playerId);

        if (isset($result['armyId'])) {
            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            $result['heroes'] = $mHeroesInGame->getForMove($armyId);
            $result['soldiers'] = $mSoldier->getForMove($armyId);
            $result['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($result);
        }

        return $result;
    }

    static public function joinArmiesAtPosition($position, $playerId, $gameId, $db)
    {
        if (!isset($position['x'])) {
            throw new Exception('(joinArmiesAtPosition) No x position - exiting');
        }

        if (!isset($position['y'])) {
            throw new Exception('(joinArmiesAtPosition) No y position - exiting');
        }

        $mArmy = new Application_Model_Army($gameId, $db);
        $result = $mArmy->getArmyIdsByPositionPlayerId($position, $playerId);

        if (!isset($result[0]['armyId'])) {
//            echo '
//(joinArmiesAtPosition) Brak armii na pozycji: ';
//            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
//            print_r($position);

            return array(
                'armyId' => null,
                'deletedIds' => null,
            );
        }

        $firstArmyId = $result[0]['armyId'];
        unset($result[0]);
        $count = count($result);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        for ($i = 1; $i <= $count; $i++) {
            if ($result[$i]['armyId'] == $firstArmyId) {
                continue;
            }
            $mHeroesInGame->heroesUpdateArmyId($result[$i]['armyId'], $firstArmyId);
            $mSoldier->soldiersUpdateArmyId($result[$i]['armyId'], $firstArmyId);
            $mArmy->destroyArmy($result[$i]['armyId'], $playerId);
        }

        return array(
            'armyId' => $firstArmyId,
            'deletedIds' => $result
        );
    }

    static public function getArmyByArmyId($armyId, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $army = $mArmy->getArmyByArmyId($armyId);

        if ($army['destroyed']) {
            $army['heroes'] = array();
            $army['soldiers'] = array();

            return $army;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $army['heroes'] = $mHeroesInGame->getForMove($army['armyId']);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $army['soldiers'] = $mSoldier->getForMove($army['armyId']);

        if (empty($army['heroes']) && empty($army['soldiers'])) {
            $army['destroyed'] = true;
            $mArmy->destroyArmy($army['armyId'], $army['playerId']);
            unset($army['playerId']);

            return $army;
        } else {
            unset($army['playerId']);
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
            return $army;
        }
    }

    public function updateArmyPosition($playerId, $path, $fields, $gameId, $db)
    {
        if (empty($path)) {
            return;
        }

        if ($this->canFly()) {
            $type = 'flying';
        } elseif ($this->canSwim()) {
            $type = 'swimming';
        } else {
            $type = 'walking';
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($this->heroes as $hero) {
            $movesSpend = 0;

            foreach ($path as $step) {
                if (!isset($step['myCastleCosts'])) {
                    $movesSpend += $this->terrain[$fields[$step['y']][$step['x']]][$type];
                }
            }

            $movesLeft = $hero['movesLeft'] - $movesSpend;
            if ($movesLeft < 0) {
                $movesLeft = 0;
            }

            $mHeroesInGame->updateMovesLeft($movesLeft, $hero['heroId']);
        }

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);

        if ($this->canFly() || $this->canSwim()) {
            foreach ($this->soldiers as $soldier) {
                $movesSpend = 0;

                foreach ($path as $step) {
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $this->terrain[$fields[$step['y']][$step['x']]][$type];
                    }
                }

                $movesLeft = $soldier['movesLeft'] - $movesSpend;
                if ($movesLeft < 0) {
                    $movesLeft = 0;
                }

                $mSoldier->updateMovesLeft($movesLeft, $soldier['soldierId']);
            }
        } else {
            foreach ($this->soldiers as $soldier) {
                $movesSpend = 0;

                $this->terrain['f'][$type] = $this->units[$soldier['unitId']]['modMovesForest'];
                $this->terrain['m'][$type] = $this->units[$soldier['unitId']]['modMovesHills'];
                $this->terrain['s'][$type] = $this->units[$soldier['unitId']]['modMovesSwamp'];

                foreach ($path as $step) {
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $this->terrain[$fields[$step['y']][$step['x']]][$type];
                    }
                }

                $movesLeft = $soldier['movesLeft'] - $movesSpend;
                if ($movesLeft < 0) {
                    $movesLeft = 0;
                }

                $mSoldier->updateMovesLeft($movesLeft, $soldier['soldierId']);
            }
        }

        $mArmy = new Application_Model_Army($gameId, $db);

        $end = end($path);
        $this->x = $end['x'];
        $this->y = $end['y'];

        return $mArmy->updateArmyPosition($playerId, $end, $this->id);
    }

    static public function getAttackSequence($gameId, $db, $playerId)
    {
        $mBattleSequence = new Application_Model_BattleSequence($gameId, $db);
        $sequence = $mBattleSequence->getAttack($playerId);
        return $sequence;
    }

    static public function getDefenceSequence($gameId, $db, $playerId)
    {
        if (empty($playerId)) {
            $playerId = 0;
        }
        $mBattleSequence = new Application_Model_BattleSequence($gameId, $db);
        $sequence = $mBattleSequence->getDefence($playerId);
        return $sequence;
    }

    static public function getAllEnemiesArmies($gameId, $db, $playerId)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);

        $result = $mArmy->getAllEnemiesArmies($playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($playerId));

        $armies = array();

        foreach ($result as $army) {
            $armies[$army['armyId']] = $army;
            $armies[$army['armyId']]['heroes'] = $mHeroesInGame->getForMove($army['armyId']);
            $armies[$army['armyId']]['soldiers'] = $mSoldier->getForMove($army['armyId']);
            if (empty($armies[$army['armyId']]['heroes']) AND empty($armies[$army['armyId']]['soldiers'])) {
                $mArmy->destroyArmy($armies[$army['armyId']]['armyId'], $playerId);
                unset($armies[$army['armyId']]);
            } else {
                $armies[$army['armyId']]['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($armies[$army['armyId']]);
            }
        }

        return $armies;
    }

    static public function getEnemyArmiesFieldsPositions($gameId, $db, $playerId)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);

        $fields = Zend_Registry::get('fields');

        foreach ($mArmy->getEnemyArmiesFieldsPositions($playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($playerId)) as $row) {
            $fields[$row['y']][$row['x']] = 'e';
        }

        return $fields;
    }

    static public function getEnemyPlayerIdFromPosition($gameId, $db, $playerId, $position)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);

        return $mArmy->getEnemyPlayerIdFromPosition($playerId, $position, $mPlayersInGame->selectPlayerTeamExceptPlayer($playerId));
    }
}
