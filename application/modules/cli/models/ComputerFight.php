<?php

class Cli_Model_ComputerFight
{
    public function __construct($gameId, $playerId, $db)
    {
        $this->_gameId = $gameId;
        $this->_playerId = $playerId;
        $this->_db = $db;

        $this->_modelArmy = new Application_Model_Army($this->_gameId, $this->_db);
    }

    public function fightEnemy($army, $path, $fields, $enemy, $castleId)
    {
        $result = array(
            'victory' => false
        );

        $position = end($path);
        $fields = Application_Model_Board::changeArmyField($fields, $position['x'], $position['y'], 'E');
        $mapCastles = Zend_Registry::get('castles');
        $mArmy2 = new Application_Model_Army($this->_gameId, $this->_db);

        if ($castleId) { // castle
            $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

            if ($mCastlesInGame->isEnemyCastle($castleId, $this->_playerId)) { // enemy castle
                $playersInGameColors = Zend_Registry::get('playersInGameColors');
                $defenderId = $mCastlesInGame->getPlayerIdByCastleId($castleId);
                $result['defenderColor'] = $playersInGameColors[$defenderId];
                $enemy = Cli_Model_Army::getCastleGarrisonFromCastlePosition($mapCastles[$castleId]['position'], $this->_gameId, $this->_db);
                $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $this->_gameId, $castleId, $this->_db);
                $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);

                $battle = new Cli_Model_Battle($army, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
                $defender = $mArmy2->getDefender($enemy['ids']);

                if (!$battle->getDefender()) {
                    Cli_Model_Army::updateArmyPosition($this->_playerId, $path, $fields, $army, $this->_gameId, $this->_db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($army['armyId'], $this->_playerId, $this->_gameId, $this->_db);
                    $result['victory'] = true;
                    $mCastlesInGame->changeOwner($mapCastles[$castleId], $this->_playerId);
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    $mArmy2->destroyArmy($army['armyId'], $this->_playerId);
                }
            } else { // neutral castle
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($this->_gameId, $this->_db);
                $enemy['defenseModifier'] = 0;
                $battle = new Cli_Model_Battle($army, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, 0));
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, 0);
                $defender = $battle->getDefender();

                if (!$battle->getDefender()) {
                    Cli_Model_Army::updateArmyPosition($this->_playerId, $path, $fields, $army, $this->_gameId, $this->_db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($army['armyId'], $this->_playerId, $this->_gameId, $this->_db);

                    $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
                    $mCastlesInGame->addCastle($castleId, $this->_playerId);
                    $result['victory'] = true;
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    $mArmy2->destroyArmy($army['armyId'], $this->_playerId);
                    $defender = null;
                }
                $result['defenderColor'] = 'neutral';
            }
        } else { // enemy army
            $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);
            $enemy = Cli_Model_Army::addTowerDefenseModifier($enemy);
            $enemy['ids'][] = $enemy['armyId'];
            $defenderId = $mArmy2->getPlayerIdFromPosition($this->_playerId, $enemy);
            $battle = new Cli_Model_Battle($army, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
            $battle->fight();
            $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
            $defender = $mArmy2->getDefender($enemy['ids']);

            if (!$battle->getDefender()) {
                Cli_Model_Army::updateArmyPosition($this->_playerId, $path, $fields, $army, $this->_gameId, $this->_db);
                $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($army['armyId'], $this->_playerId, $this->_gameId, $this->_db);
                $result['victory'] = true;
                $defender[0]['armyId'] = $enemy['armyId'];
            } else {
                $result['attackerArmy'] = array(
                    'armyId' => $army['armyId'],
                    'destroyed' => true
                );
                $mArmy2->destroyArmy($army['armyId'], $this->_playerId);
            }
            $playersInGameColors = Zend_Registry::get('playersInGameColors');
            $result['defenderColor'] = $playersInGameColors[$defenderId];
        }

        $result['defenderArmy'] = $defender;
        $result['battle'] = $battle->getResult($army, $enemy);

        return $result;
    }

    public function isEnemyStronger($army, $enemy, $castleId, $max = 30)
    {
        $attackerWinsCount = 0;
        $attackerCourage = 2;

        $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

        if ($castleId !== null && $mCastlesInGame->isEnemyCastle($castleId, $this->_playerId)) {
            $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $this->_gameId, $castleId, $this->_db);
        } else {
            $enemy = Cli_Model_Army::addTowerDefenseModifier($enemy);
        }

        $defenderId = $this->_modelArmy->getPlayerIdFromPosition($this->_playerId, $enemy);

        $attackerBattleSequence = Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId);
        $defenderBattleSequence = Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId);

        for ($i = 0; $i < $max; $i++) {
            $battle = new Cli_Model_Battle($army, $enemy, $attackerBattleSequence, $defenderBattleSequence);
            $battle->fight();
            if ($battle->getAttacker()) {
                $attackerWinsCount++;
            }
        }

        $border = $max - $attackerWinsCount - $attackerCourage;
        if ($attackerWinsCount >= $border) {
            return false;
        } else {
            return true;
        }
    }
}

