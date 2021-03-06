<?php

class Cli_Model_Battle
{
    private $_result;
    private $_attacker;
    private $_attackingFighter;
    private $_defendingFighter;
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
    private $_defendingPlayerId;

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
        $attackerBattleSequence = $this->_players->getPlayer($this->_attacker->getColor())->getAttackSequence();
        if (empty($attackerBattleSequence)) {
            $units = Zend_Registry::get('units');
            $attackerBattleSequence = $units->getKeys();
        }
        $this->_attacker->setAttackBattleSequence($attackerBattleSequence);

        if (!$this->_defenders) {
            return;
        }

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
            $defendingPlayer=$this->_players->getPlayer($this->_castleColor);
            $castle = $defendingPlayer->getCastles()->getCastle($this->_castleId);
            $this->_externalDefenceModifier = $castle->getDefense() + $defendingPlayer->isCapital($this->_castleId);
        } elseif ($this->_towerId = $this->_fields->getField($defender->getX(), $defender->getY())->getTowerId()) {
            $this->_towerColor = $this->_fields->getField($defender->getX(), $defender->getY())->getTowerColor();
            $this->_externalDefenceModifier = 1;
        }
        $this->_attackModifier = $this->_attacker->getAttackModifier();
    }

    public function fight()
    {
        $attack = $this->_attacker->getAttackBattleSequence();

        foreach ($this->_defenders as $defenderArmy) {
            $this->_defender = $this->getDefender($defenderArmy);
            $this->_defenceModifier = $this->_defender->getDefenseModifier();
            $this->_defenderColor = $this->_defender->getColor();
            $this->_defenderArmyId = $this->_defender->getId();
            $this->_defendingPlayerId = $this->_players->getPlayer($this->_defenderColor)->getId();
            $defence = $this->_defender->getDefenceBattleSequence();

            foreach ($attack['walk'] as $a => $this->_attackingFighter) {
                foreach ($defence['walk'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['walk'] as $a => $this->_attackingFighter) {
                foreach ($defence['heroes'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['walk'] as $a => $this->_attackingFighter) {
                foreach ($defence['fly'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['fly'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['walk'] as $a => $this->_attackingFighter) {
                foreach ($defence['swim'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['swim'][$d]);
                    } else {
                        unset($attack['walk'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $this->_attackingFighter) {
                foreach ($defence['walk'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $this->_attackingFighter) {
                foreach ($defence['heroes'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $this->_attackingFighter) {
                foreach ($defence['fly'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['fly'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['fly'] as $a => $this->_attackingFighter) {
                foreach ($defence['swim'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['swim'][$d]);
                    } else {
                        unset($attack['fly'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $this->_attackingFighter) {
                foreach ($defence['walk'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $this->_attackingFighter) {
                foreach ($defence['fly'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['fly'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $this->_attackingFighter) {
                foreach ($defence['heroes'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['heroes'] as $a => $this->_attackingFighter) {
                foreach ($defence['swim'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['swim'][$d]);
                    } else {
                        unset($attack['heroes'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $this->_attackingFighter) {
                foreach ($defence['walk'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['walk'][$d]);
                    } else {
                        unset($attack['swim'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $this->_attackingFighter) {
                foreach ($defence['heroes'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['heroes'][$d]);
                    } else {
                        unset($attack['swim'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $this->_attackingFighter) {
                foreach ($defence['fly'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
                        unset($defence['fly'][$d]);
                    } else {
                        unset($attack['swim'][$a]);
                        break;
                    }
                }
            }
            foreach ($attack['swim'] as $a => $this->_attackingFighter) {
                foreach ($defence['swim'] as $d => $this->_defendingFighter) {
                    $this->combat();
                    if ($this->_attackingFighter->getTmpLife() > $this->_defendingFighter->getTmpLife()) {
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
            $this->resetLife();
            return count($attack['swim']) || count($attack['heroes']) || count($attack['walk']) || count($attack['fly']);
        }
    }

    private function resetLife()
    {
        $this->_attacker->resetTmpLife();
        foreach ($this->_defenders as $defenderArmy) {
            $this->getDefender($defenderArmy)->resetTmpLife();
        }
    }

    private function combat()
    {
        $attackingFighterLife = $this->_attackingFighter->getTmpLife();
        $defendingFighterLife = $this->_defendingFighter->getTmpLife();

        $attackPoints = $this->_attackingFighter->getAttackPoints() + $this->_attackModifier;
        $defencePoints = $this->_defendingFighter->getDefensePoints() + $this->_defenceModifier + $this->_externalDefenceModifier;

        $maxDie = $attackPoints + $defencePoints;
        while ($attackingFighterLife AND $defendingFighterLife) {
            $dieAttacking = $this->rollDie($maxDie);
            $dieDefending = $this->rollDie($maxDie);

            if ($attackPoints > $dieDefending AND $defencePoints <= $dieAttacking) {
                $defendingFighterLife--;
            } elseif ($attackPoints <= $dieDefending AND $defencePoints > $dieAttacking) {
                $attackingFighterLife--;
            }
        }

        $this->_succession++;

        $this->_attackingFighter->setTmpLife($attackingFighterLife);
        $this->_defendingFighter->setTmpLife($defendingFighterLife);

        if ($this->_db) {
            if ($attackingFighterLife) {
                $id = $this->_defendingFighter->getId();
                $this->_result->getDefending($this->_defenderColor, $this->_defenderArmyId, $this->_defendingFighter->getType())->addSuccession($id, $this->_succession);

                if ($this->_defenderColor == 'neutral') {
                    return;
                }

                switch ($this->_defendingFighter->getType()) {
                    case 'hero':
                        $this->_defender->removeHero($id, $this->_attackerId, $this->_defendingPlayerId, $this->_gameId, $this->_db);
                        break;
                    case 'swim':
                        $this->_defender->removeSwimmingSoldier($id, $this->_attackerId, $this->_defendingPlayerId, $this->_gameId, $this->_db);
                        break;
                    case 'fly':
                        $this->_defender->removeFlyingSoldier($id, $this->_attackerId, $this->_defendingPlayerId, $this->_gameId, $this->_db);
                        break;
                    case 'walk':
                        $this->_defender->removeWalkingSoldier($id, $this->_attackerId, $this->_defendingPlayerId, $this->_gameId, $this->_db);
                        break;
                }
            } else {
                $id = $this->_attackingFighter->getId();
                $this->_result->getAttacking($this->_attackingFighter->getType())->addSuccession($id, $this->_succession);

                switch ($this->_attackingFighter->getType()) {
                    case 'hero':
                        $this->_attacker->removeHero($id, $this->_defendingPlayerId, $this->_attackerId, $this->_gameId, $this->_db);
                        break;
                    case 'swim':
                        $this->_attacker->removeSwimmingSoldier($id, $this->_defendingPlayerId, $this->_attackerId, $this->_gameId, $this->_db);
                        break;
                    case 'fly':
                        $this->_attacker->removeFlyingSoldier($id, $this->_defendingPlayerId, $this->_attackerId, $this->_gameId, $this->_db);
                        break;
                    case 'walk':
                        $this->_attacker->removeWalkingSoldier($id, $this->_defendingPlayerId, $this->_attackerId, $this->_gameId, $this->_db);
                        break;
                }
            }
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

    private function attackerVictory()
    {
        $victory = false;

        $heroes = $this->_attacker->getHeroes();
        foreach ($heroes->getKeys() as $heroId) {
            if (!$this->_result->getAttacking('hero')->isDead($heroId)) {
                $heroes->getHero($heroId)->updateRemainingLife($this->_gameId, $this->_db);
                $victory = true;
            }
        }

        $walking = $this->_attacker->getWalkingSoldiers();
        foreach ($walking->getKeys() as $soldierId) {
            if (!$this->_result->getAttacking('walk')->isDead($soldierId)) {
                $walking->getSoldier($soldierId)->updateRemainingLife($this->_gameId, $this->_db);
                $victory = true;
            }
        }

        $swimming = $this->_attacker->getSwimmingSoldiers();
        foreach ($swimming->getKeys() as $soldierId) {
            if (!$this->_result->getAttacking('swim')->isDead($soldierId)) {
                $swimming->getSoldier($soldierId)->updateRemainingLife($this->_gameId, $this->_db);
                $victory = true;
            }
        }

        $flying = $this->_attacker->getFlyingSoldiers();
        foreach ($flying->getKeys() as $soldierId) {
            if (!$this->_result->getAttacking('fly')->isDead($soldierId)) {
                $flying->getSoldier($soldierId)->updateRemainingLife($this->_gameId, $this->_db);
                $victory = true;
            }
        }

        return $victory;
    }

    private function defenderVictory(Cli_Model_Army $defender, $color)
    {
        $victory = false;
        $armyId = $defender->getId();

        $heroes = $defender->getHeroes();
        foreach ($heroes->getKeys() as $heroId) {
            if (!$this->_result->getDefending($color, $armyId, 'hero')->isDead($heroId)) {
                $heroes->getHero($heroId)->updateRemainingLife($this->_gameId, $this->_db);
                $victory = true;
            }
        }

        $walking = $defender->getWalkingSoldiers();
        foreach ($walking->getKeys() as $soldierId) {
            if (!$this->_result->getDefending($color, $armyId, 'walk')->isDead($soldierId)) {
                if ($color != 'neutral') {
                    $walking->getSoldier($soldierId)->updateRemainingLife($this->_gameId, $this->_db);
                }
                $victory = true;
            }
        }

        $swimming = $defender->getSwimmingSoldiers();
        foreach ($swimming->getKeys() as $soldierId) {
            if (!$this->_result->getDefending($color, $armyId, 'swim')->isDead($soldierId)) {
                $swimming->getSoldier($soldierId)->updateRemainingLife($this->_gameId, $this->_db);
                $victory = true;
            }
        }

        $flying = $defender->getFlyingSoldiers();
        foreach ($flying->getKeys() as $soldierId) {
            if (!$this->_result->getDefending($color, $armyId, 'fly')->isDead($soldierId)) {
                $flying->getSoldier($soldierId)->updateRemainingLife($this->_gameId, $this->_db);
                $victory = true;
            }
        }

        return $victory;
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

