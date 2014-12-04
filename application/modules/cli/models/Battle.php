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

    private $_castleId;
    private $_castleColor;
    private $_towerId;
    private $_towerColor;

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
        $this->_fields = $game->getFields();

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

        if ($this->_castleId = $this->_fields->getCastleId($defender->getX(), $defender->getY())) {
            $this->_castleColor = $this->_fields->getCastleColor($defender->getX(), $defender->getY());
            $this->_externalDefenceModifier = $this->_players->getPlayer($this->_fields->getCastleColor($defender->getX(), $defender->getY()))->getCastles()->getCastle($this->_castleId)->getDefenseModifier();
        } elseif ($this->_towerId = $this->_fields->getTowerId($defender->getX(), $defender->getY())) {
            $this->_towerColor = $this->_fields->getTowerColor($defender->getX(), $defender->getY());
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

        if ($this->_db) {
            if ($this->attackerVictory()) {
                $this->_result->victory();
                if ($this->_castleId) {
                    $castleOwner = $this->_players->getPlayer($this->_castleColor);
                    $this->_players->getPlayer($this->_attacker->getColor())->addCastle($this->_castleId, $castleOwner->getCastles()->getCastle($this->_castleId), $this->_castleColor, $this->_fields, $this->_gameId, $this->_db);
                    $castleOwner->removeCastle($this->_castleId);
                    $this->_result->setCastleId($this->_castleId);

                } elseif ($this->_towerId) {
                    $towerOwner = $this->_players->getPlayer($this->_towerColor);
                    $this->_players->getPlayer($this->_attacker->getColor())->addTower($this->_towerId, $towerOwner->getTowers()->getTower($this->_towerId), $this->_towerColor, $this->_fields, $this->_gameId, $this->_db);
                    $towerOwner->removeTower($this->_towerId);
                    $this->_result->setTowerId($this->_towerId);
                }

            } else {
                echo 'a';
                $this->_players->getPlayer($this->_attacker->getColor())->getArmies()->removeArmy($this->_attacker->getId(), $this->_gameId, $this->_db);
            }
            foreach ($this->_defenders as $defender) {
                $color = $defender->getColor();
                if ($color == 'neutral') {
                    continue;
                }
                $dead = true;
                foreach ($defender->getHeroes()->getKeys() as $heroId) {
                    if (!$this->_result->isDefendingHero($color, $heroId)) {
                        $dead = false;
                        break;
                    }
                }
                if (!$dead) {
                    continue;
                }

                foreach ($defender->getSoldiers()->getKeys() as $soldierId) {
                    if (!$this->_result->isDefendingSoldier($color, $soldierId)) {
                        $dead = false;
                        break;
                    }
                }
                if (!$dead) {
                    continue;
                }

                foreach ($defender->getShips()->getKeys() as $soldierId) {
                    if (!$this->_result->isDefendingSoldier($color, $soldierId)) {
                        $dead = false;
                        break;
                    }
                }
                if ($dead) {
                    $this->_players->getPlayer($color)->getArmies()->removeArmy($defender->getId(), $this->_gameId, $this->_db);
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
                    $this->_result->addDefendingHeroSuccession($this->_defender->getColor(), $heroId, $this->_succession);
                    $this->_defender->removeHero($heroId, $this->_attackerId, $this->_defenderId, $this->_gameId, $this->_db);
                } else {
                    $soldierId = $defendingFighter->getId();
                    $this->_result->addDefendingSoldierSuccession($this->_defender->getColor(), $soldierId, $this->_succession);
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
            $color = $defender->getColor();
            foreach ($defender->getHeroes()->getKeys() as $heroId) {
                $this->_result->addDefendingHero($color, $heroId);
            }

            foreach ($defender->getSoldiers()->getKeys() as $soldierId) {
                $this->_result->addDefendingSoldier($color, $soldierId);
            }

            foreach ($defender->getShips()->getKeys() as $soldierId) {
                $this->_result->addDefendingShip($color, $soldierId);
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

