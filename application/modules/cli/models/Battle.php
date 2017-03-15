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
    private $_defenderColor;
    private $_defenderArmyId;

    private $_players;

    private $_attackerId;
    private $_defenderId;

    private $_gameId;
    private $_db;

    private $_castleId;
    private $_castleColor;
    private $_towerId;
    private $_towerColor;

    public function __construct(Cli_Model_Army $attacker, Cli_Model_Enemies $defenders, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db = null, Cli_Model_BattleResult $result = null)
    {
        $this->_attacker = $attacker;
        $this->_defenders = $defenders->get();
        $this->_castleId = $defenders->getCastleId();
        $this->_castleColor = $defenders->getCastleColor();
        $this->_players = $game->getPlayers();
        $this->_fields = $game->getFields();
        if ($db) {
            $this->_gameId = $game->getId();
            $this->_game = $game;
            $this->_attackerId = $this->_players->getPlayer($this->_attacker->getColor())->getId();
            $this->_db = $db;
            $this->_result = $result;
        }

        $this->init();
    }

    private function init()
    {
        if (!$this->_defenders) {
            return;
        }
        $attackerBattleSequence = $this->_players->getPlayer($this->_attacker->getColor())->getAttackSequence();
        if (empty($attackerBattleSequence)) {
            $units = Zend_Registry::get('units');
            $attackerBattleSequence = $units->getKeys();
        }
        $this->_attacker->setAttackBattleSequence($attackerBattleSequence);
        foreach ($this->_defenders as $defender) {
            $defenderBattleSequence = $this->_players->getPlayer($defender->getColor())->getDefenceSequence();
            if (empty($defenderBattleSequence)) {
                if (!isset($units)) {
                    $units = Zend_Registry::get('units');
                }
                $defenderBattleSequence = $units->getKeys();
            }
            $defender->setDefenceBattleSequence($defenderBattleSequence);
        }

        if ($this->_castleId) {
            $castle = $this->_players->getPlayer($this->_castleColor)->getCastles()->getCastle($this->_castleId);
            $this->_externalDefenceModifier = $castle->getDefense() + $castle->isCapital();
        } elseif ($this->_towerId = $this->_fields->getField($defender->getX(), $defender->getY())->getTowerId()) {
            $this->_towerColor = $this->_fields->getField($defender->getX(), $defender->getY())->getTowerColor();
            $this->_externalDefenceModifier = 1;
        }
        $this->_attackModifier = $this->_attacker->getAttackModifier();
    }

    private function attackerVictory()
    {
        foreach ($this->_attacker->getHeroes()->getKeys() as $heroId) {
            if (!$this->_result->getAttacking('hero')->isDead($heroId)) {
                return true;
            }
        }
        foreach ($this->_attacker->getWalkingSoldiers()->getKeys() as $soldierId) {
            if (!$this->_result->getAttacking('walk')->isDead($soldierId)) {
                return true;
            }
        }
        foreach ($this->_attacker->getSwimmingSoldiers()->getKeys() as $soldierId) {
            if (!$this->_result->getAttacking('swim')->isDead($soldierId)) {
                return true;
            }
        }
        foreach ($this->_attacker->getFlyingSoldiers()->getKeys() as $soldierId) {
            if (!$this->_result->getAttacking('fly')->isDead($soldierId)) {
                return true;
            }
        }
    }

    private function defenderVictory(Cli_Model_Army $defender, $color)
    {
        $armyId = $defender->getId();
        foreach ($defender->getHeroes()->getKeys() as $heroId) {
            if (!$this->_result->getDefending($color, $armyId, 'hero')->isDead($heroId)) {
                return true;
            }
        }
        foreach ($defender->getWalkingSoldiers()->getKeys() as $soldierId) {
            if (!$this->_result->getDefending($color, $armyId, 'walk')->isDead($soldierId)) {
                return true;
            }
        }
        foreach ($defender->getSwimmingSoldiers()->getKeys() as $soldierId) {
            if (!$this->_result->getDefending($color, $armyId, 'swim')->isDead($soldierId)) {
                return true;
            }
        }
        foreach ($defender->getFlyingSoldiers()->getKeys() as $soldierId) {
            if (!$this->_result->getDefending($color, $armyId, 'fly')->isDead($soldierId)) {
                return true;
            }
        }
    }

    public function fight()
    {
        $lives = array('attack' => 2, 'defense' => 2);
        $attack = $this->_attacker->getAttackBattleSequence();

        foreach ($this->_defenders as $defenderArmy) {
            $this->_defender = $this->getDefender($defenderArmy);
            $this->_defenceModifier = $this->_defender->getDefenseModifier();
            $this->_defenderColor = $this->_defender->getColor();
            $this->_defenderArmyId = $this->_defender->getId();
            $this->_defenderId = $this->_players->getPlayer($this->_defenderColor)->getId();
            $defence = $this->_defender->getDefenceBattleSequence();

            foreach ($attack['walk'] as $a => $attackingFighter) {
                foreach ($defence['walk'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['walk'] as $a => $attackingFighter) {
                foreach ($defence['heroes'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['walk'] as $a => $attackingFighter) {
                foreach ($defence['fly'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['fly'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['walk'] as $a => $attackingFighter) {
                foreach ($defence['swim'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['swim'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $attackingFighter) {
                foreach ($defence['walk'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $attackingFighter) {
                foreach ($defence['fly'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['fly'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $attackingFighter) {
                foreach ($defence['heroes'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $attackingFighter) {
                foreach ($defence['swim'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['swim'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $attackingFighter) {
                foreach ($defence['walk'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $attackingFighter) {
                foreach ($defence['fly'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['fly'][$d]);
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
                foreach ($defence['swim'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['swim'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $attackingFighter) {
                foreach ($defence['walk'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['swim'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $attackingFighter) {
                foreach ($defence['heroes'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['swim'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $attackingFighter) {
                foreach ($defence['fly'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['fly'][$d]);
                    } else {
                        unset($attack['swim'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $attackingFighter) {
                foreach ($defence['swim'] as $d => $defendingFighter) {
                    $lives = $this->combat($attackingFighter, $defendingFighter, $lives);
                    if ($lives['attack'] > $lives['defense']) {
                        unset($defence['swim'][$d]);
                    } else {
                        unset($attack['swim'][$a]);
                        break;
                    }
                }
            }
        }

        if ($this->_db) {
            $this->saveFight();
        } else {
            return count($attack['swim']) || count($attack['heroes']) || count($attack['walk']) || count($attack['fly']);
        }
    }

    private function saveFight()
    {
        if ($this->attackerVictory()) {
            $this->_result->victory();
            if ($this->_castleId) {
                $castleOwner = $this->_players->getPlayer($this->_castleColor);
                $castle = $castleOwner->getCastles()->getCastle($this->_castleId);
                $castle->setProductionId();
                $attackerColor = $this->_attacker->getColor();
                $attackingPlayer = $this->_players->getPlayer($attackerColor);
                $attackingPlayer->getCastles()->addCastle(
                    $this->_castleId,
                    $castle,
                    $this->_castleColor,
                    $attackingPlayer->getId(),
                    $this->_gameId,
                    $this->_db
                );
                $castleX = $castle->getX();
                $castleY = $castle->getY();
                for ($x = $castleX; $x <= $castleX + 1; $x++) {
                    for ($y = $castleY; $y <= $castleY + 1; $y++) {
                        $field = $this->_fields->getField($x, $y);
                        $field->setCastleColor($attackerColor);
                    }
                }
                $castleOwner->getCastles()->removeCastle($this->_castleId);

            } elseif ($this->_towerId) {
                $towerOwner = $this->_players->getPlayer($this->_towerColor);
                $this->_players->getPlayer($this->_attacker->getColor())->addTower($this->_towerId, $towerOwner->getTowers()->getTower($this->_towerId), $this->_towerColor, $this->_fields, $this->_gameId, $this->_db);
                $towerOwner->getTowers()->removeTower($this->_towerId);
            }
            $this->_attacker->resetAttributes();

        } else {
            $this->_players->getPlayer($this->_attacker->getColor())->getArmies()->removeArmy($this->_attacker->getId(), $this->_game, $this->_db);
        }
        foreach ($this->_defenders as $defender) {
            $color = $defender->getColor();
            if ($this->defenderVictory($defender, $color)) {
                if ($color == 'neutral') {
                    continue;
                }
                $defender->resetAttributes();
            } else {
                if ($color == 'neutral') {
                    $this->_players->getPlayer($color)->getArmies()->removeArmy($defender->getId(), $this->_game);
                } else {
                    $this->_players->getPlayer($color)->getArmies()->removeArmy($defender->getId(), $this->_game, $this->_db);
                }
            }
        }
        $this->_result->setCastleId($this->_castleId);
        $this->_result->setTowerId($this->_towerId);
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
                    $this->_result->getDefending($this->_defenderColor, $this->_defenderArmyId, $defendingFighter->getType())->addSuccession($heroId, $this->_succession);
                    $this->_defender->removeHero($heroId, $this->_attackerId, $this->_defenderId, $this->_gameId, $this->_db);
                } elseif ($defendingFighter->getType() == 'swim') {
                    $soldierId = $defendingFighter->getId();
                    $this->_result->getDefending($this->_defenderColor, $this->_defenderArmyId, $defendingFighter->getType())->addSuccession($soldierId, $this->_succession);
                    if ($this->_defenderId) {
                        $this->_defender->removeSwimmingSoldier($soldierId, $this->_attackerId, $this->_defenderId, $this->_gameId, $this->_db);
                    }
                } elseif ($defendingFighter->getType() == 'fly') {
                    $soldierId = $defendingFighter->getId();
                    $this->_result->getDefending($this->_defenderColor, $this->_defenderArmyId, $defendingFighter->getType())->addSuccession($soldierId, $this->_succession);
                    if ($this->_defenderId) {
                        $this->_defender->removeFlyingSoldier($soldierId, $this->_attackerId, $this->_defenderId, $this->_gameId, $this->_db);
                    }
                } else {
                    $soldierId = $defendingFighter->getId();
                    $this->_result->getDefending($this->_defenderColor, $this->_defenderArmyId, $defendingFighter->getType())->addSuccession($soldierId, $this->_succession);
                    if ($this->_defenderId) {
                        $this->_defender->removeWalkingSoldier($soldierId, $this->_attackerId, $this->_defenderId, $this->_gameId, $this->_db);
                    }
                }
            } else {
                if ($attackingFighter->getType() == 'hero') {
                    $heroId = $attackingFighter->getId();
                    $this->_result->getAttacking($attackingFighter->getType())->addSuccession($heroId, $this->_succession);
                    $this->_attacker->removeHero($heroId, $this->_defenderId, $this->_attackerId, $this->_gameId, $this->_db);
                } elseif ($attackingFighter->getType() == 'swim') {
                    $soldierId = $attackingFighter->getId();
                    $this->_result->getAttacking($attackingFighter->getType())->addSuccession($soldierId, $this->_succession);
                    $this->_attacker->removeSwimmingSoldier($soldierId, $this->_defenderId, $this->_attackerId, $this->_gameId, $this->_db);
                } elseif ($attackingFighter->getType() == 'fly') {
                    $soldierId = $attackingFighter->getId();
                    $this->_result->getAttacking($attackingFighter->getType())->addSuccession($soldierId, $this->_succession);
                    $this->_attacker->removeFlyingSoldier($soldierId, $this->_defenderId, $this->_attackerId, $this->_gameId, $this->_db);
                } else {
                    $soldierId = $attackingFighter->getId();
                    $this->_result->getAttacking($attackingFighter->getType())->addSuccession($soldierId, $this->_succession);
                    $this->_attacker->removeWalkingSoldier($soldierId, $this->_defenderId, $this->_attackerId, $this->_gameId, $this->_db);
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
            $defender = $this->getDefender($defender);
            $color = $defender->getColor();
            $armyId = $defender->getId();
            foreach ($defender->getHeroes()->getKeys() as $heroId) {
                $this->_result->getDefending($color, $armyId, 'hero')->add($heroId);
            }
            foreach ($defender->getWalkingSoldiers()->getKeys() as $soldierId) {
                $this->_result->getDefending($color, $armyId, 'walk')->add($soldierId);
            }
            foreach ($defender->getSwimmingSoldiers()->getKeys() as $soldierId) {
                $this->_result->getDefending($color, $armyId, 'swim')->add($soldierId);
            }
            foreach ($defender->getFlyingSoldiers()->getKeys() as $soldierId) {
                $this->_result->getDefending($color, $armyId, 'fly')->add($soldierId);
            }
        }

        foreach ($this->_attacker->getHeroes()->getKeys() as $heroId) {
            $this->_result->getAttacking('hero')->add($heroId);
        }
        foreach ($this->_attacker->getWalkingSoldiers()->getKeys() as $soldierId) {
            $this->_result->getAttacking('walk')->add($soldierId);
        }
        foreach ($this->_attacker->getSwimmingSoldiers()->getKeys() as $soldierId) {
            $this->_result->getAttacking('swim')->add($soldierId);
        }
        foreach ($this->_attacker->getFlyingSoldiers()->getKeys() as $soldierId) {
            $this->_result->getAttacking('fly')->add($soldierId);
        }

        return $this->_result;
    }

    /**
     * @param Cli_Model_Army $defenderArmy
     * @return Cli_Model_Army
     */
    private function getDefender(Cli_Model_Army $defenderArmy)
    {
        return $defenderArmy;
    }
}

