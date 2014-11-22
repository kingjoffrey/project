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

    public function __construct(Cli_Model_Army $attacker, Cli_Model_Army $defender, $attackerBattleSequence, $defenderBattleSequence)
    {
        $this->attacker = $attacker;
        $this->defender = $defender;
        if (empty($attackerBattleSequence)) {
            $units = Zend_Registry::get('units');
            $attackerBattleSequence = array_keys($units);
        }
        if (empty($defenderBattleSequence)) {
            if (!isset($units)) {
                $units = Zend_Registry::get('units');
            }
            $defenderBattleSequence = array_keys($units);
        }
        if ($attacker->isemptyAttackBattleSequence()) {
            $attacker->setAttackBattleSequence($attackerBattleSequence);
        }
        if ($defender->isemptyDefenceBattleSequence()) {
            $defender->setDefenceBattleSequence($defenderBattleSequence);
        }
    }

//    public function updateArmies($gameId, $db, $attackerId = null, $defenderId = null)
//    {
//        $this->deleteHeroes($this->_result['defense']['heroes'], $gameId, $db, $attackerId, $defenderId);
//        $this->deleteSoldiers($this->_result['defense']['soldiers'], $gameId, $db, $attackerId, $defenderId);
//        $this->deleteHeroes($this->_result['attack']['heroes'], $gameId, $db, $defenderId, $attackerId);
//        $this->deleteSoldiers($this->_result['attack']['soldiers'], $gameId, $db, $defenderId, $attackerId);
//    }

//    private function deleteHeroes($heroes, $gameId, $db, $winnerId, $loserId)
//    {
//        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
//        $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
//        foreach ($heroes as $v) {
//            $mHeroesKilled->add($v['heroId'], $winnerId, $loserId);
//            $mHeroesInGame->armyRemoveHero($v['heroId']);
//        }
//    }
//
//    private function deleteSoldiers($soldiers, $gameId, $db, $winnerId, $loserId)
//    {
//        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
//        $mSoldiersKilled = new Application_Model_SoldiersKilled($gameId, $db);
//        foreach ($soldiers as $v) {
//            if (strpos($v['soldierId'], 's') === false) {
//                $mSoldiersKilled->add($v['unitId'], $winnerId, $loserId);
//                $mSoldier->destroy($v['soldierId']);
//            }
//        }
//    }

//    public function getDefender() // only used for getting neutral castle garrison
//    {
//        if (empty($this->defender['soldiers']) && empty($this->defender['heroes']) && empty($this->defender['ships'])) {
//            return array();
//        }
//
//        return array(
//            'soldiers' => array_merge($this->defender['soldiers'], $this->defender['ships']),
//            'heroes' => $this->defender['heroes']
//        );
//    }

    public function attackerVictory()
    {
        return $this->attacker->attackerVictory();
    }

    public function fight()
    {
        $lives = array('attack' => 2, 'defense' => 2);

        $attack = $this->attacker->getAttack();
        $defence = $this->defender->getDefence();

        foreach ($attack['soldiers'] as $a => $soldierAttack) {
            foreach ($defence['soldiers'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['soldiers'][$d]);
                } else {
                    unset($attack['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['soldiers'] as $a => $soldierAttack) {
            foreach ($defence['heroes'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['heroes'][$d]);
                } else {
                    unset($attack['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['soldiers'] as $a => $soldierAttack) {
            foreach ($defence['ships'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['ships'][$d]);
                } else {
                    unset($attack['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['heroes'] as $a => $soldierAttack) {
            foreach ($defence['soldiers'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['soldiers'][$d]);
                } else {
                    unset($attack['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['heroes'] as $a => $soldierAttack) {
            foreach ($defence['heroes'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['heroes'][$d]);
                } else {
                    unset($attack['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['heroes'] as $a => $soldierAttack) {
            foreach ($defence['ships'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['ships'][$d]);
                } else {
                    unset($attack['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['ships'] as $a => $soldierAttack) {
            foreach ($defence['soldiers'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['soldiers'][$d]);
                } else {
                    unset($attack['ships'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['ships'] as $a => $soldierAttack) {
            foreach ($defence['heroes'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['heroes'][$d]);
                } else {
                    unset($attack['ships'][$a]);
                    break;
                }
            }
        }
        foreach ($attack['ships'] as $a => $soldierAttack) {
            foreach ($defence['ships'] as $d => $soldierDefence) {
                $lives = $this->combat($soldierAttack, $soldierDefence, $lives);
                if ($lives['attack'] > $lives['defense']) {
                    unset($defence['ships'][$d]);
                } else {
                    unset($attack['ships'][$a]);
                    break;
                }
            }
        }
    }

    private function combat($attackingFighter, $defendingFighter, $lives)
    {
        $attackLives = $lives['attack'];
        $defenseLives = $lives['defense'];

        if (!$attackLives) {
            $attackLives = 2;
        }

        if (!$defenseLives) {
            $defenseLives = 2;
        }

        $attackPoints = $attackingFighter->getAttackPoints() + $this->attacker->getAttackModifier();
        $defencePoints = $defendingFighter->getDefensePoints() + $this->defender->getDefenseModifier();

        $maxDie = $attackPoints + $defencePoints;
        while ($attackLives AND $defenseLives) {
            $dieAttacking = $this->rollDie($maxDie);
            $dieDefending = $this->rollDie($maxDie);

//            echo '$unitAttacking[\'attackPoints\']=' . $unitAttacking['attackPoints'] . "\n";
//            echo '$dieDefending=' . $dieDefending . "\n";
//            echo '$unitDefending[\'defensePoints\']=' . $unitDefending['defensePoints'] . "\n";
//            echo '$dieAttacking=' . $dieAttacking . "\n\n";

            if ($attackPoints > $dieDefending AND $defencePoints <= $dieAttacking) {
                $defenseLives--;
            } elseif ($attackPoints <= $dieDefending AND $defencePoints > $dieAttacking) {
                $attackLives--;
            }
        }

        $this->succession++;

        if ($attackLives) {
            if ($defendingFighter->getType() == 'hero') {
                $this->_result['defense']['heroes'][] = array(
                    'heroId' => $defendingFighter->getId(),
                    'succession' => $this->succession
                );
            } else {
                $this->_result['defense']['soldiers'][] = array(
                    'soldierId' => $defendingFighter->getId(),
                    'unitId' => $defendingFighter->getUnitId(),
                    'succession' => $this->succession
                );
            }
        } else {
            if ($attackingFighter->getType() == 'hero') {
                $this->_result['attack']['heroes'][] = array(
                    'heroId' => $attackingFighter->getId(),
                    'succession' => $this->succession
                );
            } else {
                $this->_result['attack']['soldiers'][] = array(
                    'soldierId' => $attackingFighter->getId(),
                    'unitId' => $attackingFighter->getUnitId(),
                    'succession' => $this->succession
                );
            }
        }

        return array('attack' => $attackLives, 'defense' => $defenseLives);
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
}

