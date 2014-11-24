<?php

class Cli_Model_Fight
{
    public function __construct(Cli_Model_Game $game, Cli_Model_Army $army, Cli_Model_Path $path, $playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $l = new Coret_Model_Logger();
        $l->logMethodName();
        $result = new Cli_Model_FightResult();
        $gameId = $game->getId();
        $fields = $game->getFields();
        $enemies = array();

        if ($castleId = $fields->getCastleId($path->x, $path->y)) {
            $defenderColor = $fields->getCastleColor($path->x, $path->y);
            $result->setDefenderColor($defenderColor);
            $player = $game->getPlayer($game->getPlayerId($defenderColor));
            if ($defenderColor == 'neutral') {
                $enemies[] = $player->getCastleGarrison($game->getTurnNumber(), $game->getFirstUnitId());
            } else {
                $enemies = $player->getCastleGarrison($castleId);
            }
        } elseif ($enemyArmies = $fields->getArmies($path->x, $path->y)) {
            foreach ($enemyArmies as $armyId => $color) {
                $enemies[] = $game->getPlayerArmy($game->getPlayerId($color), $armyId);
            }
        } else {

        }


        if (isset($path->castleId) && $path->castleId) { // castle
            if ($mCastlesInGame->isEnemyCastle($path->castleId, $this->_playerId)) { // enemy castle
                $defenderId = $mCastlesInGame->getPlayerIdByCastleId($path->castleId);
                $result->defenderColor = $playersInGameColors[$defenderId];
                $enemy = new Cli_Model_Army(Cli_Model_Army::getCastleGarrisonFromCastlePosition($this->_map['hostileCastles'][$path->castleId]['position'], $this->_gameId, $this->_db));
                $enemy->addCastleDefenseModifier($path->castleId, $this->_gameId, $this->_db);
                $enemy->setCombatDefenseModifiers();

                $battle = new Cli_Model_Battle($this->_Computer, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
                $result->defenderArmy = $this->_mArmyDB->getDefender($enemy->ids);

                if (!$battle->getDefender()) {
                    $this->_Computer->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                    $result->attackerArmy = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_Computer->id, $this->_playerId, $this->_gameId, $this->_db);
                    $result->victory = true;
                    $mCastlesInGame->changeOwner($this->_map['hostileCastles'][$path->castleId], $this->_playerId);
                } else {
                    $result->attackerArmy = array(
                        'armyId' => $this->_Computer->id,
                        'destroyed' => true
                    );
                    $this->_mArmyDB->destroyArmy($this->_Computer->id, $this->_playerId);
                }
            } else { // neutral castle
                $battle = new Cli_Model_Battle(
                    $this->_Computer,
                    ,
                    Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId),
                    Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, 0)
                );
                $battle->fight();
                $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, 0);
                $result->defenderArmy = $battle->getDefender();

                if (!$battle->getDefender()) {
                    $this->_Computer->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                    $result->attackerArmy = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_Computer->id, $this->_playerId, $this->_gameId, $this->_db);

                    $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
                    $mCastlesInGame->addCastle($path->castleId, $this->_playerId);
                    $result->victory = true;
                } else {
                    $result->attackerArmy = array(
                        'armyId' => $this->_Computer->id,
                        'destroyed' => true
                    );
                    $this->_mArmyDB->destroyArmy($this->_Computer->id, $this->_playerId);
                }
                $result->defenderColor = 'neutral';
            }
        } else { // enemy army
            $enemy = new Cli_Model_Army($this->_mArmyDB->getAllEnemyUnitsFromPosition($path->end, $this->_playerId));
            $enemy->setCombatDefenseModifiers();
            $enemy->addTowerDefenseModifier();
            $defenderId = $enemy->getEnemyPlayerId($this->_gameId, $this->_playerId, $this->_db);
            $battle = new Cli_Model_Battle($this->_Computer, $enemy, Cli_Model_Army::getAttackSequence($this->_gameId, $this->_db, $this->_playerId), Cli_Model_Army::getDefenceSequence($this->_gameId, $this->_db, $defenderId));
            $battle->fight();
            $battle->updateArmies($this->_gameId, $this->_db, $this->_playerId, $defenderId);
            $result->defenderArmy = $this->_mArmyDB->getDefender($enemy->ids);

            if (!$battle->getDefender()) {
                $this->_Computer->updateArmyPosition($this->_playerId, $path, $fields, $this->_gameId, $this->_db);
                $result->attackerArmy = Cli_Model_Army::getArmyByArmyIdPlayerId($this->_Computer->id, $this->_playerId, $this->_gameId, $this->_db);
                $result->victory = true;
//                $defender[0]['armyId'] = $enemy->id;
            } else {
                $result->attackerArmy = array(
                    'armyId' => $this->_Computer->id,
                    'destroyed' => true
                );
                $this->_mArmyDB->destroyArmy($this->_Computer->id, $this->_playerId);
            }
            $playersInGameColors = Zend_Registry::get('playersInGameColors');
            $result->defenderColor = $playersInGameColors[$defenderId];
        }

        $result->battle = $battle->getResult();

        return $result;
    }
}

