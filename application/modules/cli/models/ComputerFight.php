<?php

class Cli_Model_ComputerFight
{
    public function fightEnemy($path)
    {
        $result = array(
            'victory' => false
        );

        $fields = Application_Model_Board::changeArmyField($this->_map['fields'], $path->x, $path->y, 'E');

        if (isset($path->castleId) && $path->castleId) { // castle
            $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

            if ($mCastlesInGame->isEnemyCastle($path->castleId, $this->_playerId)) { // enemy castle
                $playersInGameColors = Zend_Registry::get('playersInGameColors');
                $defenderId = $mCastlesInGame->getPlayerIdByCastleId($path->castleId);
                $result['defenderColor'] = $playersInGameColors[$defenderId];
                $enemy = new Cli_Model_Army(Cli_Model_Army::getCastleGarrisonFromCastlePosition($this->_map['hostileCastles'][$path->castleId]['position'], $this->_gameId, $this->_db));
                $enemy->addCastleDefenseModifier($path->castleId, $this->_gameId, $this->_db);
                $enemy->setCombatDefenseModifiers();

                $battle = new Cli_Model_Battle($this->_mArmy, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
                $defender = $this->_mArmyDB->getDefender($enemy['ids']);

                if (!$battle->getDefender()) {
                    $this->_mArmy->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_mArmy->id, $this->_playerId, $this->_gameId, $this->_db);
                    $result['victory'] = true;
                    $mCastlesInGame->changeOwner($this->_map['hostileCastles'][$path->castleId], $this->_playerId);
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $this->_mArmy->id,
                        'destroyed' => true
                    );
                    $this->_mArmyDB->destroyArmy($this->_mArmy->id, $this->_playerId);
                }
            } else { // neutral castle
                $enemy = new Cli_Model_Army(Cli_Model_Battle::getNeutralCastleGarrison($this->_gameId, $this->_db));
                $battle = new Cli_Model_Battle($this->_mArmy, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, 0));
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, 0);
                $defender = $battle->getDefender();

                if (!$battle->getDefender()) {
                    $this->_mArmy->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_mArmy->id, $this->_playerId, $this->_gameId, $this->_db);

                    $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
                    $mCastlesInGame->addCastle($path->castleId, $this->_playerId);
                    $result['victory'] = true;
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $this->_mArmy->id,
                        'destroyed' => true
                    );
                    $this->_mArmyDB->destroyArmy($this->_mArmy->id, $this->_playerId);
                    $defender = null;
                }
                $result['defenderColor'] = 'neutral';
            }
        } else { // enemy army
            $enemy = new Cli_Model_Army($this->_mArmyDB->getAllEnemyUnitsFromPosition($path->end, $this->_playerId));
            $enemy->setCombatDefenseModifiers();
            $enemy->addTowerDefenseModifier();
            $defenderId = $enemy->getEnemyPlayerIdFromPosition($this->_gameId, $this->_db, $this->_playerId);
            $battle = new Cli_Model_Battle($this->_mArmy, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
            $battle->fight();
            $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
            $defender = $this->_mArmyDB->getDefender($enemy['ids']);

            if (!$battle->getDefender()) {
                $this->_mArmy->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_mArmy->id, $this->_playerId, $this->_gameId, $this->_db);
                $result['victory'] = true;
                $defender[0]['armyId'] = $enemy['armyId'];
            } else {
                $result['attackerArmy'] = array(
                    'armyId' => $this->_mArmy->id,
                    'destroyed' => true
                );
                $this->_mArmyDB->destroyArmy($this->_mArmy->id, $this->_playerId);
            }
            $playersInGameColors = Zend_Registry::get('playersInGameColors');
            $result['defenderColor'] = $playersInGameColors[$defenderId];
        }

        $result['defenderArmy'] = $defender;
        $result['battle'] = $battle->getResult();

        return $result;
    }

    public function isEnemyStronger(Cli_Model_Army $enemy, $castleId = null, $max = 30)
    {
        $attackerWinsCount = 0;
        $attackerCourage = 2;

        $enemy->setCombatDefenseModifiers();
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

        if ($castleId !== null && $mCastlesInGame->isEnemyCastle($castleId, $this->_playerId)) {
            $enemy->addCastleDefenseModifier($this->_gameId, $castleId, $this->_db);
        } else {
            $enemy->addTowerDefenseModifier();
        }

        $defenderId = $enemy->getEnemyPlayerId($this->_gameId, $this->_playerId, $this->_db);

        $attackerBattleSequence = Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId);
        $defenderBattleSequence = Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId);

        for ($i = 0; $i < $max; $i++) {
            $battle = new Cli_Model_Battle($this->_mArmy, $enemy, $attackerBattleSequence, $defenderBattleSequence);
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

