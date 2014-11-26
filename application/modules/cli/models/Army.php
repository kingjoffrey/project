<?php

class Cli_Model_Army
{

    public $_id;
    public $_x;
    public $_y;
    public $_fortified;

    public $_ids;

    public $_attackModifier;
    public $_defenseModifier;

    public $_attackBattleSequence = array(
        'soldiers' => array(),
        'ships' => array(),
        'heroes' => array()
    );
    public $_defenceBattleSequence = array(
        'soldiers' => array(),
        'ships' => array(),
        'heroes' => array()
    );

    public $_heroes = array();
    public $_soldiers = array();
    public $_ships = array();

    private $_canFly = 0;
    private $_canSwim = 0;

    private $_units;
    private $_terrain;

    private $_movesLeft = 1000;

    /*
     * @param array $army
     */
    public function __construct($army)
    {
        if (!isset($army['armyId'])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no "armyId"');
        }
        $this->_id = $army['armyId'];

        if (!isset($army['x']) || !isset($army['y'])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no "x" or "y"');
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
        $units = Zend_Registry::get('units');
        foreach ($soldiers as $soldier) {
            $unit = $units[$soldier['unitId']];
            if ($unit['canSwim']) {
                $this->_ships[$soldier['soldierId']] = new Cli_Model_Soldier($soldier, $unit);
            } else {
                $this->_soldiers[$soldier['soldierId']] = new Cli_Model_Soldier($soldier, $unit);
            }
        }
    }

    public function init()
    {
        $numberOfHeroes = 0;
        $attackFlyModifier = 0;

        foreach ($this->_heroes as $hero) {
            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->_movesLeft = $hero->getMovesLeft();
            }
            $numberOfHeroes++;
        }

        if ($numberOfHeroes) {
            $this->_attackModifier->increment();
            $this->_defenseModifier->increment();
        }

        $this->_canFly = -$numberOfHeroes + 1;

        foreach ($this->_soldiers as $soldier) {
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->_movesLeft = $soldier->getMovesLeft();
            }

            if ($soldier->canFly()) {
                $attackFlyModifier++;
                $this->_canFly++;
            } else {
                $this->_canFly--;
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

    private function shipsToArray()
    {
        $ships = array();
        foreach ($this->_ships as $soldierId => $soldier) {
            $ships[$soldierId] = $soldier->toArray();
        }
        return $ships;
    }

    public function toArray()
    {
        return array(
            'armyId' => $this->_id,
            'soldiers' => $this->soldiersToArray(),
            'ships' => $this->shipsToArray(),
            'heroes' => $this->heroesToArray(),
            'x' => $this->_x,
            'y' => $this->_y,
            'fortified' => false,
            'destroyed' => false,
            'canFly' => $this->canFly(),
            'canSwim' => $this->canSwim(),
            'movesLeft' => $this->_movesLeft
        );
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
        $movesLeft = 1000;

        foreach ($this->_soldiers as $soldier) {
            if (!$soldier->canSwim()) {
                continue;
            }

            if ($movesLeft > $soldier->getMovesLeft()) {
                $movesLeft = $soldier->getMovesLeft();
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

            foreach ($this->_soldiers as $soldierId => $soldier) {
                if (!isset($soldiersMovesLeft[$soldierId])) {
                    $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                }

                if ($fullPath[$i]['tt'] == 'f') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesForest'];
                } elseif ($fullPath[$i]['tt'] == 's') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($fullPath[$i]['tt'] == 'm') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesHills'];
                } elseif (!isset($fullPath[$i]['cc'])) {
                    $soldiersMovesLeft[$soldierId] -= $defaultMoveCost;
                }

                if ($soldiersMovesLeft[$soldierId] < 0) {
                    $skip = true;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    $stop = true;
                    break;
                }
            }

            foreach ($this->_heroes as $heroId => $hero) {
                if (!isset($heroesMovesLeft[$heroId])) {
                    $heroesMovesLeft[$heroId] = $hero->getMovesLeft();
                }

                if (!isset($fullPath[$i]['cc'])) {
                    $heroesMovesLeft[$heroId] -= $defaultMoveCost;
                }

                if ($heroesMovesLeft[$heroId] < 0) {
                    $skip = true;
                }

                if ($heroesMovesLeft[$heroId] <= 0) {
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

    public function setDefenseModifier($defenseModifier)
    {
        $this->_defenseModifier->set($defenseModifier);
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

        foreach ($this->_soldiers as $soldierId => $soldier) {
            // ustawiam początkową ilość ruchów dla każdej jednostki
            if (!isset($soldiersMovesLeft[$soldierId])) {
                $soldiersMovesLeft[$soldierId] = $this->_units[$soldier['unitId']]['numberOfMoves'];
                if ($soldier->getMovesLeft() <= 2) {
                    $soldiersMovesLeft[$soldierId] += $soldier->getMovesLeft();
                } else {
                    $soldiersMovesLeft[$soldierId] += 2;
                }
            }

            foreach ($fullPath as $step) {
                // odejmuję
                if ($step['tt'] == 'f') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesForest'];
                } elseif ($step['tt'] == 's') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($step['tt'] == 'm') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesHills'];
                } else {
                    if ($this->_units[$soldier['unitId']]['canFly']) {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['flying'];
                    } elseif ($this->_units[$soldier['unitId']]['canSwim']) {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['swimming'];
                    } else {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['walking'];

                    }
                }

                if ($step['tt'] == 'E') {
                    break;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    break;
                }
            }
        }

        foreach ($this->_heroes as $heroId => $hero) {
            if (!isset($heroesMovesLeft[$heroId])) {
                $heroesMovesLeft[$heroId] = $hero['numberOfMoves'];
                if ($hero->getMovesLeft() <= 2) {
                    $heroesMovesLeft[$heroId] += $hero->getMovesLeft();
                } elseif ($hero->getMovesLeft() > 2) {
                    $heroesMovesLeft[$heroId] += 2;
                }
            }

            foreach ($fullPath as $step) {
                $heroesMovesLeft[$heroId] -= $this->_terrain[$step['tt']]['walking'];

                if ($step['tt'] == 'E') {
                    break;
                }

                if ($heroesMovesLeft[$heroId] <= 0) {
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

    public function updateArmyPosition($gameId, Cli_Model_Path $path, Cli_Model_Fields $fields, Zend_Db_Adapter_Pdo_Pgsql $db)
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

        foreach ($this->_heroes as $heroId => $hero) {
            $movesSpend = 0;

            foreach ($path->current as $step) {
                if ($step['x'] == $this->_x && $step['y'] == $this->_y) {
                    break;
                }
                if (!isset($step['myCastleCosts'])) {
                    $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
                }
            }

            $hero->updateMovesLeft($heroId, $movesSpend, $mHeroesInGame);

            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->setMovesLeft($hero->getMovesLeft());
            }
        }

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);

        if ($this->canFly() || $this->canSwim()) {
            foreach ($this->_soldiers as $soldierId => $soldier) {
                $movesSpend = 0;

                foreach ($path->current as $step) {
                    if ($step['x'] == $this->_x && $step['y'] == $this->_y) {
                        break;
                    }
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
                    }
                }

                $soldier->updateMovesLeft($soldierId, $movesSpend, $mSoldier);

                if ($this->_movesLeft > $soldier->getMovesLeft()) {
                    $this->setMovesLeft($soldier->getMovesLeft());
                }
            }
        } else {
            foreach ($this->_soldiers as $soldierId => $soldier) {
                $movesSpend = 0;

                $this->_terrain['f'][$type] = $soldier->getForest();
                $this->_terrain['m'][$type] = $soldier->getHills();
                $this->_terrain['s'][$type] = $soldier->getSwamp();

                foreach ($path->current as $step) {
                    if ($step['x'] == $this->_x && $step['y'] == $this->_y) {
                        break;
                    }
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
                    }
                }

                $soldier->updateMovesLeft($soldierId, $movesSpend, $mSoldier);

                if ($this->_movesLeft > $soldier->getMovesLeft()) {
                    $this->setMovesLeft($soldier->getMovesLeft());
                }
            }
        }

        $mArmy = new Application_Model_Army($gameId, $db);

        $this->_x = $path->x;
        $this->_y = $path->y;

        return $mArmy->updateArmyPosition($path->end, $this->_id);
    }

    public function getX()
    {
        return $this->_x;
    }

    public function getY()
    {
        return $this->_y;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getMovesLeft()
    {
        return $this->_movesLeft;
    }

    public function setMovesLeft($movesLeft)
    {
        $this->_movesLeft = $movesLeft;
    }

    public function addHeroes($heroes)
    {
        if (empty($this->_heroes) && $heroes) {
            $this->_attackModifier->increment();
            $this->_defenseModifier->increment();
        }
        $this->_heroes = array_merge($this->_heroes, $heroes);
    }

    public function getHeroes()
    {
        return $this->_heroes;
    }

    public function addSoldiers($soldiers)
    {
        $this->_soldiers = array_merge($this->_soldiers, $soldiers);
    }

    public function getSoldiers()
    {
        return $this->_soldiers;
    }

    public function createSoldier($gameId, $playerId, $unitId, $db)
    {
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $soldierId = $mSoldier->add($this->_id, $unitId);

        $this->_soldiers[$soldierId] = new Cli_Model_Soldier(array('unitId' => $unitId, 'soldierId' => $soldierId), $this->_units[$unitId]);

        $mSoldiersCreated = new Application_Model_SoldiersCreated($gameId, $db);
        $mSoldiersCreated->add($unitId, $playerId);
    }

    public function createHero($gameId, $heroId, $db)
    {
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->addToArmy($this->_id, $heroId, 0);

        $this->_heroes[$heroId] = new Cli_Model_Hero($mHeroesInGame->getForMove($this->_id));
    }

    public function resetMovesLeft($gameId, $db)
    {
        foreach ($this->_heroes as $hero) {
            $hero->resetMovesLeft($gameId, $db);
        }
        foreach ($this->_soldiers as $soldier) {
            $soldier->resetMovesLeft($gameId, $db);
        }
    }

    public function setFortified($fortified, $gameId, $db)
    {
        $this->_fortified = $fortified;
        $mArmy = new Application_Model_Army($gameId, $db);
        $mArmy->fortify($this->getId(), $fortified);
    }

    public function getFortified()
    {
        return $this->_fortified;
    }

    public function hasHero()
    {
        if ($this->_heroes) {
            return true;
        }
    }

    public function getAnyHeroId()
    {
        reset($this->_heroes);
        return key($this->_heroes);
    }

    public function zeroHeroMovesLeft($heroId, $gameId, $db)
    {
        $this->_heroes[$heroId]->zeroMovesLeft($gameId, $db);
    }

    public function removeHero($heroId, $playerId, $gameId, $db)
    {
        $this->_heroes[$heroId]->kill($playerId, $gameId, $db);
        unset($this->_heroes[$heroId]);
        if (empty($this->_heroes)) {
            $this->_attackModifier->decrement();
            $this->_defenseModifier->decrement();
        }
    }

    public function getNumberOfSoldiers()
    {
        return count($this->_soldiers);
    }

    public function getNumberOfHeroes()
    {
        return count($this->_heroes);
    }

    public function count()
    {
        return count($this->_soldiers) + count($this->_ships) + count($this->_heroes);
    }

    public function setAttackBattleSequence($attackBattleSequence)
    {
        foreach ($this->_soldiers as $soldier) {
            $soldier->setUsed(false);
        }
        foreach ($attackBattleSequence as $unitId) {
            foreach ($this->_soldiers as $soldier) {
                if ($soldier->getUnitId() == $unitId && $soldier->notUsed()) {
                    $soldier->setUsed(true);
                    $this->_attackBattleSequence['soldiers'][] = $soldier;
                }
            }
        }

        foreach ($this->_heroes as $hero) {
            $this->_attackBattleSequence['heroes'][] = $hero;
        }

        foreach ($this->_ships as $ship) {
            $this->_attackBattleSequence['ships'][] = $ship;
        }
    }

    public function setDefenceBattleSequence($defenceBattleSequence)
    {
        foreach ($this->_soldiers as $soldier) {
            $soldier->setUsed(false);
        }
        foreach ($defenceBattleSequence as $unitId) {
            foreach ($this->_soldiers as $soldier) {
                if ($soldier->getUnitId() == $unitId && $soldier->notUsed()) {
                    $soldier->setUsed(true);
                    $this->_defenceBattleSequence['soldiers'][] = $soldier;
                }
            }
        }

        foreach ($this->_heroes as $hero) {
            $this->_defenceBattleSequence['heroes'][] = $hero;
        }

        foreach ($this->_ships as $ship) {
            $this->_defenceBattleSequence['ships'][] = $ship;
        }
    }

    public function getAttackBattleSequence()
    {
        return $this->_attackBattleSequence;
    }

    public function getDefenceBattleSequence()
    {
        return $this->_defenceBattleSequence;
    }

    public function getAttackModifier()
    {
        return $this->_attackModifier->get();
    }

    public function getDefenseModifier()
    {
        return $this->_defenseModifier->get();
    }

    public function attackerVictory()
    {
        return count($this->_attackBattleSequence['soldiers']) || count($this->_attackBattleSequence['heroes']) || count($this->_attackBattleSequence['ships']);
    }

    public function getCosts()
    {
        $costs = 0;
        foreach ($this->_soldiers as $soldier) {
            $costs += $soldier->getCost();
        }
        foreach ($this->_ships as $soldier) {
            $costs += $soldier->getCost();
        }
        return $costs;
    }
}
