<?php

class Cli_Model_Battle
{

    private $_result;
    private $_attacker;
    private $_defender;
    private $_defenders;
    private $_succession = 0;
    private $_externalDefenceModifier;
    private $_attackModifier;
    private $_defenceModifier;

    private $_players;

    private $_attackerId;
    private $_defenderId;

    private $_gameId;
    private $_db;

    public function __construct(Cli_Model_Army $attacker, $defenders, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_attacker = $attacker;
        $this->_defenders = $defenders;

        $this->_result = new Cli_Model_BattleResult();

        $this->_players = $game->getPlayers();

        if ($db) {
            $this->_gameId = $game->getId();
            $this->_attackerId = $this->_players->getPlayer($this->_attacker->getColor())->getId();
            $this->_db = $db;
        }
        $fields = $game->getFields();

        $attackerBattleSequence = $this->_players->getPlayer($this->_attacker->getColor())->getAttackSequence();
        if (empty($attackerBattleSequence)) {
            $units = Zend_Registry::get('units');
            $attackerBattleSequence = array_keys($units);
        }
        $this->_attacker->setAttackBattleSequence($attackerBattleSequence);

        foreach ($this->_defenders as $defender) {
            $defenderBattleSequence = $this->_players->getPlayer($defender->getColor())->getDefenceSequence();
            if (empty($defenderBattleSequence)) {
                if (!isset($units)) {
                    $units = Zend_Registry::get('units');
                }
                $defenderBattleSequence = array_keys($units);
            }
            $defender->setDefenceBattleSequence($defenderBattleSequence);
        }

        if ($castleId = $fields->getCastleId($defender->getX(), $defender->getY())) {
            $this->_externalDefenceModifier = $this->_players->getPlayer($fields->getCastleColor($defender->getX(), $defender->getY()))->getCastle($castleId)->getDefenseModifier();
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
            $this->_defender = $defenderArmy;
            $this->_defenceModifier = $this->_defender->getDefenseModifier();
            $this->_defenderId = $this->_players->getPlayer($this->_defender->getColor())->getId();
            $defence = $this->_defender->getDefenceBattleSequence();

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

        if ($this->_db) {
            if ($attackLives) {
                if ($defendingFighter->getType() == 'hero') {
                    $heroId = $defendingFighter->getId();
                    $this->_result->addDefendingHeroSuccession($heroId, $this->_succession);
                    $this->_defender->removeHero($heroId, $this->_attackerId, $this->_defenderId, $this->_gameId, $this->_db);
                } else {
                    $soldierId = $defendingFighter->getId();
                    $this->_result->addDefendingSoldierSuccession($soldierId, $this->_succession);
                    if ($this->_defenderId) {
                        $this->_defender->removeSoldier($soldierId, $this->_attackerId, $this->_defenderId, $this->_gameId, $this->_db);
                    }
                }
            } else {
                if ($attackingFighter->getType() == 'hero') {
                    $heroId = $attackingFighter->getId();
                    $this->_result->addAttackingHeroSuccession($heroId, $this->_succession);
                    $this->_attacker->removeHero($heroId, $this->_defenderId, $this->_attackerId, $this->_gameId, $this->_db);
                } else {
                    $soldierId = $attackingFighter->getId();
                    $this->_result->addAttackingSoldierSuccession($soldierId, $this->_succession);
                    $this->_attacker->removeSoldier($soldierId, $this->_defenderId, $this->_attackerId, $this->_gameId, $this->_db);
                }
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
        foreach ($this->_defenders as $defender) {
            foreach ($defender->getHeroes()->getKeys() as $heroId) {
                $this->_result->addDefendingHero($heroId);
            }

            foreach ($defender->getSoldiers()->getKeys() as $soldierId) {
                $this->_result->addDefendingSoldier($soldierId);
            }

            foreach ($defender->getShips()->getKeys() as $soldierId) {
                $this->_result->addDefendingShip($soldierId);
            }
        }

        foreach ($this->_attacker->getHeroes()->getKeys() as $heroId) {
            $this->_result->addAttackingHero($heroId);
        }

        foreach ($this->_attacker->getSoldiers()->getKeys() as $soldierId) {
            $this->_result->addAttackingSoldier($soldierId);
        }

        foreach ($this->_attacker->getShips()->getKeys() as $soldierId) {
            $this->_result->addAttackingShip($soldierId);
        }

        return $this->_result;
    }
}

