<?php

class Cli_Model_ArmyFunctions
{

    static public function armyArray($columnName = '')
    {
        return array('armyId', 'destroyed', 'fortified', 'x', 'y', $columnName);
    }

    static public function calculateMaxArmyMoves($army)
    {
        foreach ($army['heroes'] as $hero) {
            if (!isset($heroMoves)) {
                $heroMoves = $hero['movesLeft'];
            }

            if ($hero['movesLeft'] < $heroMoves) {
                $heroMoves = $hero['movesLeft'];
            }
        }

        foreach ($army['soldiers'] as $soldier) {
            if (!isset($soldierMoves)) {
                $soldierMoves = $soldier['movesLeft'];
            }

            if ($soldier['movesLeft'] < $soldierMoves) {
                $soldierMoves = $soldier['movesLeft'];
            }
        }

        if (!isset($heroMoves)) {
            $heroMoves = 0;
        }

        if (!isset($soldierMoves)) {
            $soldierMoves = 0;
        }

        if ($heroMoves > $soldierMoves) {
            return $heroMoves;
        } else {
            return $soldierMoves;
        }
    }

    static public function heroResurrection($gameId, $heroId, $position, $playerId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armyId = $mArmy->getArmyIdFromPosition($position);
        if (!$armyId) {
            $armyId = $mArmy->createArmy($position, $playerId);
        }
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->addToArmy($armyId, $heroId, 0);

        return $armyId;
    }

    static public function getCastleGarrisonFromCastlePosition($castlePosition, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $ids = $mArmy->getCastleGarrisonFromCastlePosition($castlePosition);

        if ($ids) {
            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            return array(
                'heroes' => $mHeroesInGame->getForBattle($ids),
                'soldiers' => $mSoldier->getForBattle($ids),
                'ids' => $ids,
                'x' => $castlePosition['x'],
                'y' => $castlePosition['y']
            );
        } else {
            return array(
                'heroes' => array(),
                'soldiers' => array(),
                'ids' => array(0),
                'x' => $castlePosition['x'],
                'y' => $castlePosition['y']
            );
        }

    }

    /*
     * @return array
     */
    static public function getArmiesFromCastlePosition($castlePosition, $gameId, $playerId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armies = $mArmy->getArmiesFromCastlePosition($castlePosition, $playerId);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($armies as $k => $army) {
            $armies[$k]['heroes'] = $mHeroesInGame->getForMove($army['armyId']);
            $armies[$k]['soldiers'] = $mSoldier->getForMove($army['armyId']);
        }

        return $armies;
    }

    static public function isCastleGarrisonSufficient($expectedNumberOfUnits, $armiesInCastle)
    {
        foreach ($armiesInCastle as $army) {
            if (count($army['soldiers']) == $expectedNumberOfUnits && count($army['heroes']) == 0) {
                return $army['armyId'];
            }
        }
    }

    static public function getArmyByArmyIdPlayerId($armyId, $playerId, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $result = $mArmy->getArmyByArmyIdPlayerId($armyId, $playerId);

        if (isset($result['armyId'])) {
            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            $result['heroes'] = $mHeroesInGame->getForMove($armyId);
            $result['soldiers'] = $mSoldier->getForMove($armyId);
            $result['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($result);
        }

        return $result;
    }

    static public function getArmyByArmyId($armyId, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $army = $mArmy->getArmyByArmyId($armyId);

        if ($army['destroyed']) {
            $army['heroes'] = array();
            $army['soldiers'] = array();

            return $army;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $army['heroes'] = $mHeroesInGame->getForMove($army['armyId']);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $army['soldiers'] = $mSoldier->getForMove($army['armyId']);

        if (empty($army['heroes']) && empty($army['soldiers'])) {
            $army['destroyed'] = true;
            $mArmy->destroyArmy($army['armyId'], $army['playerId']);
            unset($army['playerId']);

            return $army;
        } else {
            unset($army['playerId']);
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
            return $army;
        }
    }

    static public function getAttackSequence($gameId, $db, $playerId)
    {
        $mBattleSequence = new Application_Model_BattleSequence($gameId, $db);
        $sequence = $mBattleSequence->getAttack($playerId);
        return $sequence;
    }

    static public function getDefenceSequence($gameId, $db, $playerId)
    {
        if (empty($playerId)) {
            $playerId = 0;
        }
        $mBattleSequence = new Application_Model_BattleSequence($gameId, $db);
        $sequence = $mBattleSequence->getDefence($playerId);
        return $sequence;
    }

    static public function getAllEnemiesArmies($gameId, $db, $playerId)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);

        $result = $mArmy->getAllEnemiesArmies($playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($playerId));

        $armies = array();

        foreach ($result as $army) {
            $army['heroes'] = $mHeroesInGame->getForMove($army['armyId']);
            $army['soldiers'] = $mSoldier->getForMove($army['armyId']);
            if (empty($armies[$army['armyId']]['heroes']) AND empty($armies[$army['armyId']]['soldiers'])) {
                $mArmy->destroyArmy($army['armyId'], $playerId);
            } else {
//                $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($armies[$army['armyId']]);
                $armies[$army['armyId']] = new Cli_Model_Army($army);
            }
        }

        return $armies;
    }

    static public function getEnemyArmiesFieldsPositions($gameId, $db, $playerId)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);

        $fields = Zend_Registry::get('fields');

        foreach ($mArmy->getEnemyArmiesFieldsPositions($playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($playerId)) as $row) {
            $fields[$row['y']][$row['x']] = 'e';
        }

        return $fields;
    }
}
