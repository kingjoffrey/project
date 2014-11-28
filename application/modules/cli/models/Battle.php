<?php

class Cli_Model_Battle
{

    private $_result;
    private $_attacker;
    private $_defenders;
    private $_succession = 0;
    private $_real;
    private $_externalDefenceModifier;
    private $_attackModifier;
    private $_defenceModifier;

    public function __construct(Cli_Model_Army $attacker, $defenders, Cli_Model_Game $game, $real = false)
    {
        $this->_attacker = $attacker;
        $this->_defenders = $defenders;

        $this->_result = new Cli_Model_BattleResult();
        $this->_real = $real;

        $players = $game->getPlayers();
        $fields = $game->getFields();

        $attackerBattleSequence = $players->getPlayer($fields->getArmyColor($this->_attacker->getX(), $this->_attacker->getY(), $this->_attacker->getId()))->getAttackSequence();
        if (empty($attackerBattleSequence)) {
            $units = Zend_Registry::get('units');
            $attackerBattleSequence = array_keys($units);
        }
        $this->_attacker->setAttackBattleSequence($attackerBattleSequence);

        foreach ($this->_defenders as $defender) {
            $defenderBattleSequence = $players->getPlayer($fields->getArmyColor($defender->getX(), $defender->getY(), $defender->getId()))->getDefenceSequence();
            if (empty($defenderBattleSequence)) {
                if (!isset($units)) {
                    $units = Zend_Registry::get('units');
                }
                $defenderBattleSequence = array_keys($units);
            }
            $defender->setDefenceBattleSequence($defenderBattleSequence);
        }

        if ($castleId = $fields->getCastleId($defender->getX(), $defender->getY())) {
            $this->_externalDefenceModifier = $players->getPlayer($fields->getCastleColor($defender->getX(), $defender->getY()))->getCastle($castleId)->getDefenseModifier();
        } elseif ($fields->getTowerId($defender->getX(), $defender->getY())) {
            $this->_externalDefenceModifier = 1;
        }

        $this->_attackModifier = $this->_attacker->getAttackModifier();
    }

    public function attackerVictory()
    {
        return $this->_attacker->attackerVictory();
    }

    public function fight()
    {
        $lives = array('attack' => 2, 'defense' => 2);

        $attack = $this->_attacker->getAttackBattleSequence();

        foreach ($this->_defenders as $defenderArmy) {
            $this->_defenceModifier = $defenderArmy->getDefenseModifier();
            $defence = $defenderArmy->getDefenceBattleSequence();

            foreach ($attack['soldiers'] as $a => $attackingFighter) {
                foreach ($defence['soldiers'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['soldiers'][$d]);
                    } else {
                        unset($attack['soldiers'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['soldiers'] as $a => $attackingFighter) {
                foreach ($defence['heroes'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['soldiers'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['soldiers'] as $a => $attackingFighter) {
                foreach ($defence['ships'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['ships'][$d]);
                    } else {
                        unset($attack['soldiers'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $attackingFighter) {
                foreach ($defence['soldiers'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['soldiers'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $attackingFighter) {
                foreach ($defence['heroes'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $attackingFighter) {
                foreach ($defence['ships'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['ships'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['ships'] as $a => $attackingFighter) {
                foreach ($defence['soldiers'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['soldiers'][$d]);
                    } else {
                        unset($attack['ships'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['ships'] as $a => $attackingFighter) {
                foreach ($defence['heroes'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['ships'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['ships'] as $a => $attackingFighter) {
                foreach ($defence['ships'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['ships'][$d]);
                    } else {
                        unset($attack['ships'][$a]);
                        break;
                    }
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

        $attackPoints = $attackingFighter->getAttackPoints() + $this->_attackModifier;
        $defencePoints = $defendingFighter->getDefensePoints() + $this->_defenceModifier + $this->_externalDefenceModifier;

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

        $this->_succession++;

        if ($this->_real) {
            if ($attackLives) {
                if ($defendingFighter->getType() == 'hero') {
                    $this->_result->addDefendingHeroSuccession($defendingFighter->getId(), $this->_succession);
                } else {
                    $this->_result->addDefendingSoldierSuccession($defendingFighter->getId(), $this->_succession);
                }
            } else {
                if ($attackingFighter->getType() == 'hero') {
                    $this->_result->addAttackingHeroSuccession($attackingFighter->getId(), $this->_succession);
                } else {
                    $this->_result->addAttackingSoldierSuccession($attackingFighter->getId(), $this->_succession);
                }
            }
        }

        return array('attack' => $attackLives, 'defense' => $defenseLives);
    }

    private function rollDie($maxDie)
    {
        return rand(1, $maxDie);
    }

    public function saveResult($gameId, $db)
    {
        foreach ($this->_defenders as $defender) {
            foreach (array_keys($defender->getHeroes()) as $unitId) {
                if ($this->_result->addDefendingHero($unitId)) {
                    $defender->removeHero($unitId, $playerId, $gameId, $db);
                }
            }

            foreach (array_keys($defender->getSoldiers()) as $soldierId) {
                $this->_result->addDefendingSoldier($soldierId);
            }

            foreach (array_keys($defender->getShips()) as $soldierId) {
                $this->_result->addDefendingShip($soldierId);
            }
        }

        foreach (array_keys($this->_attacker->getHeroes()) as $unitId) {
            if ($this->_result->addAttackingHero($unitId)) {
                $this->_attacker->removeHero($unitId, $playerId, $gameId, $db);
            }
        }

        foreach (array_keys($this->_attacker->getSoldiers()) as $soldierId) {
            $this->_result->addAttackingSoldier($soldierId);
        }

        foreach (array_keys($this->_attacker->getShips()) as $soldierId) {
            $this->_result->addAttackingShip($soldierId);
        }
    }

    public function getResult()
    {
        return $this->_result;
    }
}

