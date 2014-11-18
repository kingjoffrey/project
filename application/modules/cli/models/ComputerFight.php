<?php

class Cli_Model_ComputerFight
{
    protected $_gameId;
    protected $_playerId;
    protected $_db;
    protected $_mArmyDB;
    protected $_Computer;
    protected $_gameHandler;
    protected $_l;
    protected $_mGame;
    protected $_turnNumber;
    protected $_map;
    protected $_enemies;

    public function __construct($gameId, $playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_gameId = $gameId;
        $this->_playerId = $playerId;
        $this->_db = $db;
        $this->_mArmyDB = new Application_Model_Army($this->_gameId, $this->_db);
    }

    public function fightEnemy($path)
    {
        $this->_l->logMethodName();
        $result = new Cli_Model_FightResult();

        $fields = Application_Model_Board::changeArmyField($this->_map['fields'], $path->x, $path->y, 'E');

        if (isset($path->castleId) && $path->castleId) { // castle
            $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

            if ($mCastlesInGame->isEnemyCastle($path->castleId, $this->_playerId)) { // enemy castle
                $playersInGameColors = Zend_Registry::get('playersInGameColors');
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
                    new Cli_Model_Army(Cli_Model_Battle::getNeutralCastleGarrison($this->_gameId, $this->_db)),
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

