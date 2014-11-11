<?php

class Cli_Model_Army
{

    public $_id;
    public $_x;
    public $_y;
    public $_fortified;

    public $_ids;

    public $_defenseModifier;
    public $_attackModifier;

    public $_heroes = array();
    public $_soldiers = array();

    private $_canFly = 0;
    private $_canSwim = 0;

    private $_units;
    private $_terrain;

    /*
     * @param array $army
     */
    public function __construct($army)
    {
        if (isset($army['ids'][0])) {
            $this->_id = $army['ids'][0];
            $this->_ids = $army['ids'];
        } else {
            if (!isset($army['armyId'])) {
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                throw new Exception('no armyId');
            }
            $this->_id = $army['armyId'];
            $this->_ids = array($this->_id);
        }

        if (!isset($army['x']) || !isset($army['y'])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('');
        }

        $this->_x = $army['x'];
        $this->_y = $army['y'];

        $this->_units = Zend_Registry::get('units');
        $this->_terrain = Zend_Registry::get('terrain');

        $this->_attackModifier = new Cli_Model_AttackModifier();
        $this->_defenseModifier = new Cli_Model_DefenseModifier();
    }

    public function setHeroes($heroes)
    {
        foreach ($heroes as $hero) {
            $this->_heroes[$hero['heroId']] = new Cli_Model_Hero($hero);
        }
    }

    public function setSoldiers($soldiers)
    {
        foreach ($soldiers as $soldier) {
            $this->_soldiers[$soldier['soldierId']] = new Cli_Model_Soldier($soldier);
        }
    }

    public function init()
    {
        $modMovesForest = 0;
        $modMovesSwamp = 0;
        $modMovesHills = 0;

        $numberOfHeroes = count($this->_heroes);
        if ($numberOfHeroes) {
            $this->_attackModifier->increment();
            $modMovesForest = 3;
            $modMovesSwamp = 4;
            $modMovesHills = 5;
        }

        $this->_canFly = -$numberOfHeroes + 1;
        $this->_canSwim = 0;

        $attackFlyModifier = 0;

        foreach ($this->_soldiers as $soldier) {
            if ($soldier->getForest() > $modMovesForest) {
                $modMovesForest = $soldier->getForest();
            }
            if ($soldier->getSwamp() > $modMovesSwamp) {
                $modMovesSwamp = $soldier->getSwamp();
            }
            if ($soldier->getHills() > $modMovesHills) {
                $modMovesHills = $soldier->getHills();
            }

            if ($soldier->canFly()) {
                $attackFlyModifier++;
                $this->_canFly++;
            } else {
                $this->_canFly -= 1;
            }
            if ($soldier->canSwim()) {
                $this->_canSwim++;
            }
        }

        if ($attackFlyModifier) {
            $this->_attackModifier->increment();
        }
    }

    private function heroesToArray()
    {
        $heroes = array();
        foreach ($this->_heroes as $heroId => $hero) {
            $heroes[$heroId] = $hero->toArray();
        }
        return $heroes;
    }

    private function soldiersToArray()
    {
        $soldiers = array();
        foreach ($this->_soldiers as $soldierId => $soldier) {
            $soldiers[$soldierId] = $soldier->toArray();
        }
        return $soldiers;
    }

    public function toArray()
    {
        return array(
            'armyId' => $this->_id,
            'soldiers' => $this->soldiersToArray(),
            'heroes' => $this->heroesToArray(),
            'x' => $this->_x,
            'y' => $this->_y,
            'fortified' => false,
            'destroyed' => false,
            'canFly' => $this->canFly(),
            'canSwim' => $this->canSwim(),
//            'movesLeft' => $this->_movesLeft
        );
    }

    public function getArmyId()
    {
        return $this->_id;
    }

    public function calculateMovesSpend($fullPath)
    {
        if (empty($fullPath)) {
            return new Cli_Model_Path();
        }
        if ($this->canFly()) {
            $currentPath = $this->calculateMovesSpendFlying($fullPath);
        } elseif ($this->canSwim()) {
            $currentPath = $this->calculateMovesSpendSwimming($fullPath);
        } else {
            $currentPath = $this->calculateMovesSpendWalking($fullPath);
        }

        return new Cli_Model_Path($currentPath, $fullPath);
    }

    private function calculateMovesSpendFlying($fullPath)
    {
        $currentPath = array();

        foreach ($this->_soldiers as $soldier) {
            if (!$soldier->canFly()) {
                continue;
            }

            if (!isset($movesLeft)) {
                $movesLeft = $soldier->getMovesLeft();
                continue;
            }

            if ($movesLeft > $soldier->getMovesLeft()) {
                $movesLeft = $soldier->getMovesLeft();
            }
        }

        for ($i = 0; $i < count($fullPath); $i++) {
            if (!isset($fullPath[$i]['cc'])) {
                $movesLeft -= $this->_terrain[$fullPath[$i]['tt']]['flying'];
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

        foreach ($this->_soldiers as $soldier) {
            if (!$this->_units[$soldier['unitId']]['canSwim']) {
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
                $movesLeft -= $this->_terrain[$fullPath[$i]['tt']]['swimming'];
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
            $defaultMoveCost = $this->_terrain[$fullPath[$i]['tt']]['walking'];

            foreach ($this->_soldiers as $soldier) {
                if (!isset($soldiersMovesLeft[$soldier['soldierId']])) {
                    $soldiersMovesLeft[$soldier['soldierId']] = $soldier['movesLeft'];
                }

                if ($fullPath[$i]['tt'] == 'f') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->_units[$soldier['unitId']]['modMovesForest'];
                } elseif ($fullPath[$i]['tt'] == 's') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->_units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($fullPath[$i]['tt'] == 'm') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->_units[$soldier['unitId']]['modMovesHills'];
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

            foreach ($this->_heroes as $hero) {
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

    public function setCombatDefenseModifiers()
    {
        if ($this->canFly()) {
            $this->defenseModifier++;
        }
    }

    public function addTowerDefenseModifier()
    {
        if (Application_Model_Board::isTowerAtPosition($this->_x, $this->_y)) {
            $this->defenseModifier++;
        }
    }

    public function addCastleDefenseModifier($castleId, $gameId, $db)
    {
        $mapCastles = Zend_Registry::get('castles');

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $defenseModifier = $mapCastles[$castleId]['defense'] + $mCastlesInGame->getCastleDefenseModifier($castleId);

        if ($defenseModifier > 0) {
            $this->defenseModifier += $defenseModifier;
        } else {
            throw new Exception('$defenseModifier <= 0');
            echo 'error! !';
        }
    }

    public function canSwim()
    {
        if ($this->_canSwim) {
            return true;
        }
    }

    public function canFly()
    {
        if ($this->_canFly > 0) {
            return true;
        }
    }

    public function unitsHaveRange($fullPath)
    {
        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();

        foreach ($this->_soldiers as $soldier) {
            // ustawiam początkową ilość ruchów dla każdej jednostki
            if (!isset($soldiersMovesLeft[$soldier['soldierId']])) {
                $soldiersMovesLeft[$soldier['soldierId']] = $this->_units[$soldier['unitId']]['numberOfMoves'];
                if ($soldier['movesLeft'] <= 2) {
                    $soldiersMovesLeft[$soldier['soldierId']] += $soldier['movesLeft'];
                } else {
                    $soldiersMovesLeft[$soldier['soldierId']] += 2;
                }
            }

            foreach ($fullPath as $step) {
                // odejmuję
                if ($step['tt'] == 'f') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->_units[$soldier['unitId']]['modMovesForest'];
                } elseif ($step['tt'] == 's') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->_units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($step['tt'] == 'm') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->_units[$soldier['unitId']]['modMovesHills'];
                } else {
                    if ($this->_units[$soldier['unitId']]['canFly']) {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->_terrain[$step['tt']]['flying'];
                    } elseif ($this->_units[$soldier['unitId']]['canSwim']) {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->_terrain[$step['tt']]['swimming'];
                    } else {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->_terrain[$step['tt']]['walking'];

                    }
                }

                if ($step['tt'] == 'E') {
                    break;
                }

                if ($soldiersMovesLeft[$soldier['soldierId']] <= 0) {
                    break;
                }
            }
        }

        foreach ($this->_heroes as $hero) {
            if (!isset($heroesMovesLeft[$hero['heroId']])) {
                $heroesMovesLeft[$hero['heroId']] = $hero['numberOfMoves'];
                if ($hero['movesLeft'] <= 2) {
                    $heroesMovesLeft[$hero['heroId']] += $hero['movesLeft'];
                } elseif ($hero['movesLeft'] > 2) {
                    $heroesMovesLeft[$hero['heroId']] += 2;
                }
            }

            foreach ($fullPath as $step) {
                $heroesMovesLeft[$hero['heroId']] -= $this->_terrain[$step['tt']]['walking'];

                if ($step['tt'] == 'E') {
                    break;
                }

                if ($heroesMovesLeft[$hero['heroId']] <= 0) {
                    break;
                }
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

    public function updateArmyPosition($playerId, Cli_Model_Path $path, $fields, $gameId, $db)
    {
        if (empty($path->current)) {
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

        foreach ($this->_heroes as $hero) {
            $movesSpend = 0;

            foreach ($path->current as $step) {
                if (!isset($step['myCastleCosts'])) {
                    $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
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
            foreach ($this->_soldiers as $soldier) {
                $movesSpend = 0;

                foreach ($path->current as $step) {
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
                    }
                }

                $movesLeft = $soldier['movesLeft'] - $movesSpend;
                if ($movesLeft < 0) {
                    $movesLeft = 0;
                }

                $mSoldier->updateMovesLeft($movesLeft, $soldier['soldierId']);
            }
        } else {
            foreach ($this->_soldiers as $soldier) {
                $movesSpend = 0;

                $this->_terrain['f'][$type] = $this->_units[$soldier['unitId']]['modMovesForest'];
                $this->_terrain['m'][$type] = $this->_units[$soldier['unitId']]['modMovesHills'];
                $this->_terrain['s'][$type] = $this->_units[$soldier['unitId']]['modMovesSwamp'];

                foreach ($path->current as $step) {
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
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

        $this->_x = $path->x;
        $this->_y = $path->y;

        return $mArmy->updateArmyPosition($playerId, $path->end, $this->_id);
    }

    /*
     * @todo co jeśli jest więcej niż 1 armia na pozycji (np 2 graczy z tej samej drużyny)?
     */
    public function getEnemyPlayerId($gameId, $playerId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);

        return $mArmy->getEnemyPlayerIdFromPosition($this->_x, $this->_y, $playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($playerId));
    }

    public function getX()
    {
        return $this->_x;
    }

    public function getY()
    {
        return $this->_y;
    }
}
