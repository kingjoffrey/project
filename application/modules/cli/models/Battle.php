<?php

class Cli_Model_Battle
{

    private $_result = array(
        'defense' => array(
            'heroes' => array(),
            'soldiers' => array(),
        ),
        'attack' => array(
            'heroes' => array(),
            'soldiers' => array(),
        ),
    );
    private $attacker = array();
    private $defender = array();
    private $attackerCopy = array();
    private $defenderCopy = array();
    private $succession = 0;
    private $units;

    public function __construct($attacker, $defender, $attackerBattleSequence, $defenderBattleSequence)
    {
        $this->units = Zend_Registry::get('units');

        if (empty($defenderBattleSequence)) {
            $defenderBattleSequence = array_keys($this->units);
        }

        if (empty($attackerBattleSequence)) {
            $attackerBattleSequence = array_keys($this->units);
        }

        $this->defender = array(
            'soldiers' => array(),
            'ships' => array(),
            'defenseModifier' => $defender['defenseModifier'],
            'heroes' => $defender['heroes']
        );

        $this->attacker = array(
            'soldiers' => array(),
            'ships' => array(),
            'attackModifier' => $attacker['attackModifier'],
            'heroes' => $attacker['heroes']
        );

        foreach ($defenderBattleSequence as $unitId) {
            foreach ($defender['soldiers'] as $k => $soldier) {
                if ($this->units[$soldier['unitId']]['canSwim']) {
                    $this->defender['ships'][] = $soldier;
                    unset($defender['soldiers'][$k]);
                    continue;
                }
                if ($soldier['unitId'] == $unitId) {
                    $this->defender['soldiers'][] = $soldier;
                    unset($defender['soldiers'][$k]);
                }
            }
        }

        foreach ($attackerBattleSequence as $unitId) {
            foreach ($attacker['soldiers'] as $k => $soldier) {
                if ($this->units[$soldier['unitId']]['canSwim']) {
                    $this->attacker['ships'][] = $soldier;
                    unset($attacker['soldiers'][$k]);
                    continue;
                }
                if ($soldier['unitId'] == $unitId) {
                    $this->attacker['soldiers'][] = $soldier;
                    unset($attacker['soldiers'][$k]);
                }
            }
        }

        $this->defenderCopy = $this->defender;
        $this->attackerCopy = $this->attacker;
    }

    public function updateArmies($gameId, $db, $attackerId = null, $defenderId = null)
    {
        $this->deleteHeroes($this->_result['defense']['heroes'], $gameId, $db, $attackerId, $defenderId);
        $this->deleteSoldiers($this->_result['defense']['soldiers'], $gameId, $db, $attackerId, $defenderId);
        $this->deleteHeroes($this->_result['attack']['heroes'], $gameId, $db, $defenderId, $attackerId);
        $this->deleteSoldiers($this->_result['attack']['soldiers'], $gameId, $db, $defenderId, $attackerId);
    }

    private function deleteHeroes($heroes, $gameId, $db, $winnerId, $loserId)
    {
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
        foreach ($heroes as $v) {
            $mHeroesKilled->add($v['heroId'], $winnerId, $loserId);
            $mHeroesInGame->armyRemoveHero($v['heroId']);
        }
    }

    private function deleteSoldiers($soldiers, $gameId, $db, $winnerId, $loserId)
    {
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($gameId, $db);
        foreach ($soldiers as $v) {
            if (strpos($v['soldierId'], 's') === false) {
                $mSoldiersKilled->add($v['unitId'], $winnerId, $loserId);
                $mSoldier->destroy($v['soldierId']);
            }
        }
    }

    public function getDefender()
    {
        if (empty($this->defender['soldiers']) && empty($this->defender['heroes']) && empty($this->defender['ships'])) {
            return null;
        }
        // only neutral castle garrison (ships?)
        return $this->defender;
    }

    public function getAttacker()
    {
        if (empty($this->attacker['soldiers']) && empty($this->attacker['heroes']) && empty($this->attacker['ships'])) {
            return null;
        }
        // (ships?)
        return $this->attacker;
    }

    public function fight()
    {
        $hits = array('attack' => 2, 'defense' => 2);

        foreach ($this->attacker['soldiers'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $this->units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['soldiers'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $this->units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['soldiers'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $this->units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['soldiers'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $this->units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['ships'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $this->units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['ships'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttacking) {
            foreach ($this->defender['soldiers'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $this->units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttacking) {
            foreach ($this->defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttacking) {
            foreach ($this->defender['ships'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $this->units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['ships'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['ships'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $this->units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['soldiers'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $this->units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['ships'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['ships'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $this->units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['ships'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['ships'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $this->units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['ships'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $this->units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['ships'][$d]);
                } else {
                    unset($this->attacker['ships'][$a]);
                    break;
                }
            }
        }
    }

    private function combat($unitAttacking, $unitDefending, $hits)
    {
        $attackHits = $hits['attack'];
        $defenseHits = $hits['defense'];

        if (!$attackHits) {
            $attackHits = 2;
        }

        if (!$defenseHits) {
            $defenseHits = 2;
        }

        $unitAttacking['attackPoints'] += $this->attacker['attackModifier'];
        $unitDefending['defensePoints'] += $this->defender['defenseModifier'];

        while ($attackHits AND $defenseHits) {
            $maxDie = $unitAttacking['attackPoints'] + $unitDefending['defensePoints'];
            $dieAttacking = $this->rollDie($maxDie);
            $dieDefending = $this->rollDie($maxDie);

//            echo '$unitAttacking[\'attackPoints\']=' . $unitAttacking['attackPoints'] . "\n";
//            echo '$dieDefending=' . $dieDefending . "\n";
//            echo '$unitDefending[\'defensePoints\']=' . $unitDefending['defensePoints'] . "\n";
//            echo '$dieAttacking=' . $dieAttacking . "\n\n";

            if ($unitAttacking['attackPoints'] > $dieDefending AND $unitDefending['defensePoints'] <= $dieAttacking) {
                $defenseHits--;
            } elseif ($unitAttacking['attackPoints'] <= $dieDefending AND $unitDefending['defensePoints'] > $dieAttacking) {
                $attackHits--;
            }
        }

        $this->succession++;

        if ($attackHits) {
            if (isset($unitDefending['heroId'])) {
                $this->_result['defense']['heroes'][] = array(
                    'heroId' => $unitDefending['heroId'],
                    'succession' => $this->succession
                );
            } else {
                $this->_result['defense']['soldiers'][] = array(
                    'soldierId' => $unitDefending['soldierId'],
                    'unitId' => $unitDefending['unitId'],
                    'succession' => $this->succession
                );
            }
        } else {
            if (isset($unitAttacking['heroId'])) {
                $this->_result['attack']['heroes'][] = array(
                    'heroId' => $unitAttacking['heroId'],
                    'succession' => $this->succession
                );
            } else {
                $this->_result['attack']['soldiers'][] = array(
                    'soldierId' => $unitAttacking['soldierId'],
                    'unitId' => $unitAttacking['unitId'],
                    'succession' => $this->succession
                );
            }
        }

        return array('attack' => $attackHits, 'defense' => $defenseHits);
    }

    private function rollDie($maxDie)
    {
        return rand(1, $maxDie);
    }

    public function getResult()
    {
        $battle = array(
            'defense' => array(
                'heroes' => array(),
                'soldiers' => array(),
            ),
            'attack' => array(
                'heroes' => array(),
                'soldiers' => array(),
            ),
        );

        foreach ($this->defenderCopy['heroes'] as $unit) {
            $succession = null;
            foreach ($this->_result['defense']['heroes'] as $battleUnit) {
                if ($battleUnit['heroId'] == $unit['heroId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['defense']['heroes'][] = array(
                'heroId' => $unit['heroId'],
                'succession' => $succession
            );
        }

        foreach ($this->defenderCopy['soldiers'] as $unit) {
            $succession = null;
            foreach ($this->_result['defense']['soldiers'] as $battleUnit) {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['defense']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'unitId' => $unit['unitId'],
            );
        }

        foreach ($this->defenderCopy['ships'] as $unit) {
            $succession = null;
            foreach ($this->_result['defense']['soldiers'] as $battleUnit) {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['defense']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'unitId' => $unit['unitId'],
            );
        }

        foreach ($this->attackerCopy['heroes'] as $unit) {
            $succession = null;
            foreach ($this->_result['attack']['heroes'] as $battleUnit) {
                if ($battleUnit['heroId'] == $unit['heroId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['attack']['heroes'][] = array(
                'heroId' => $unit['heroId'],
                'succession' => $succession,
            );
        }

        foreach ($this->attackerCopy['soldiers'] as $unit) {
            $succession = null;
            foreach ($this->_result['attack']['soldiers'] as $battleUnit) {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['attack']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'unitId' => $unit['unitId'],
            );
        }

        foreach ($this->attackerCopy['ships'] as $unit) {
            $succession = null;
            foreach ($this->_result['attack']['soldiers'] as $battleUnit) {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['attack']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'unitId' => $unit['unitId'],
            );
        }

        return $battle;
    }

    static public function getNeutralCastleGarrison($gameId, $db)
    {
        $firstUnitId = Zend_Registry::get('firstUnitId');

        $mGame = new Application_Model_Game($gameId, $db);
        $turnNumber = $mGame->getTurnNumber();

        $numberOfSoldiers = ceil($turnNumber / 10);
        $soldiers = array();
        for ($i = 1; $i <= $numberOfSoldiers; $i++) {
            $soldiers[] = array(
                'defensePoints' => 3,
                'soldierId' => 's' . $i,
                'unitId' => $firstUnitId
            );
        }
        return array(
            'soldiers' => $soldiers,
            'heroes' => array(),
            'ids' => array(),
            'defenseModifier' => 0
        );
    }
}

