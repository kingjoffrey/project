<?php

class Cli_Model_ComputerFight
{
    protected $_gameId;
    protected $_playerId;
    protected $_db;
    protected $_mArmyDB;

    public function __construct($gameId, $playerId, $db)
    {
        $this->_gameId = $gameId;
        $this->_playerId = $playerId;
        $this->_db = $db;
        $this->_mArmyDB = new Application_Model_Army($this->_gameId, $this->_db);
    }

    public function fightEnemy($path, $castleId = null)
    {
        $result = array(
            'victory' => false
        );

        $position = end($path);
        $fields = Application_Model_Board::changeArmyField($this->_map['fields'], $position['x'], $position['y'], 'E');

        if ($castleId) { // castle
            $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

            if ($mCastlesInGame->isEnemyCastle($castleId, $this->_playerId)) { // enemy castle
                $playersInGameColors = Zend_Registry::get('playersInGameColors');
                $defenderId = $mCastlesInGame->getPlayerIdByCastleId($castleId);
                $result['defenderColor'] = $playersInGameColors[$defenderId];
                $enemy = Cli_Model_Army::getCastleGarrisonFromCastlePosition($this->_map['hostileCastles'][$castleId]['position'], $this->_gameId, $this->_db);
                $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $this->_gameId, $castleId, $this->_db);
                $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);

                $battle = new Cli_Model_Battle($this->_army, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
                $defender = $this->_mArmyDB->getDefender($enemy['ids']);

                if (!$battle->getDefender()) {
                    $this->_mArmy->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_army['armyId'], $this->_playerId, $this->_gameId, $this->_db);
                    $result['victory'] = true;
                    $mCastlesInGame->changeOwner($this->_map['hostileCastles'][$castleId], $this->_playerId);
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $this->_army['armyId'],
                        'destroyed' => true
                    );
                    $this->_mArmyDB->destroyArmy($this->_army['armyId'], $this->_playerId);
                }
            } else { // neutral castle
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($this->_gameId, $this->_db);
                $enemy['defenseModifier'] = 0;
                $battle = new Cli_Model_Battle($this->_army, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, 0));
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, 0);
                $defender = $battle->getDefender();

                if (!$battle->getDefender()) {
                    $this->_mArmy->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_army['armyId'], $this->_playerId, $this->_gameId, $this->_db);

                    $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
                    $mCastlesInGame->addCastle($castleId, $this->_playerId);
                    $result['victory'] = true;
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $this->_army['armyId'],
                        'destroyed' => true
                    );
                    $this->_mArmyDB->destroyArmy($this->_army['armyId'], $this->_playerId);
                    $defender = null;
                }
                $result['defenderColor'] = 'neutral';
            }
        } else { // enemy army
            $enemy = $this->_mArmyDB->getAllEnemyUnitsFromPosition($position, $this->_playerId);
            $enemy['armyId'] = $enemy['ids'][0];
            $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);
            $enemy = Cli_Model_Army::addTowerDefenseModifier($enemy);
            $defenderId = Cli_Model_Army::getEnemyPlayerIdFromPosition($this->_gameId, $this->_db, $this->_playerId, $enemy);
            $battle = new Cli_Model_Battle($this->_army, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
            $battle->fight();
            $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
            $defender = $this->_mArmyDB->getDefender($enemy['ids']);

            if (!$battle->getDefender()) {
                $this->_mArmy->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_army['armyId'], $this->_playerId, $this->_gameId, $this->_db);
                $result['victory'] = true;
                $defender[0]['armyId'] = $enemy['armyId'];
            } else {
                $result['attackerArmy'] = array(
                    'armyId' => $this->_army['armyId'],
                    'destroyed' => true
                );
                $this->_mArmyDB->destroyArmy($this->_army['armyId'], $this->_playerId);
            }
            $playersInGameColors = Zend_Registry::get('playersInGameColors');
            $result['defenderColor'] = $playersInGameColors[$defenderId];
        }

        $result['defenderArmy'] = $defender;
        $result['battle'] = $battle->getResult();

        return $result;
    }

    public function isEnemyStronger($enemy, $castleId = null, $max = 30)
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

        $defenderId = Cli_Model_Army::getEnemyPlayerIdFromPosition($this->_gameId, $this->_db, $this->_playerId, $enemy);

        $attackerBattleSequence = Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId);
        $defenderBattleSequence = Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId);

        for ($i = 0; $i < $max; $i++) {
            $battle = new Cli_Model_Battle($this->_army, $enemy, $attackerBattleSequence, $defenderBattleSequence);
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

