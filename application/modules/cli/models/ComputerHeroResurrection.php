<?php

class Cli_Model_ComputerHeroResurrection
{
    static public function handle($gameId, $playerId, $db, $gameHandler)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $gold = $mPlayersInGame->getPlayerGold($playerId);

        if ($gold < 100) {
            return;
        }

        $capitals = Zend_Registry::get('capitals');
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $color = $playersInGameColors[$playerId];
        $castleId = $capitals[$color];

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        if (!$mCastlesInGame->isPlayerCastle($castleId, $playerId)) {
            return;
        }

        $mapCastles = Zend_Registry::get('castles');

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $heroId = $mHeroesInGame->getDeadHeroId($playerId);

        if (!$heroId) {
            return;
        }

        $armyId = Cli_Model_Army::heroResurrection($gameId, $heroId, $mapCastles[$castleId]['position'], $playerId, $db);

        if (!$armyId) {
            return;
        }

        $l = new Coret_Model_Logger();
        $l->log('WSKRZESZAM HEROSA id = ' . $heroId);

        $gold -= 100;
        $mPlayersInGame->updatePlayerGold($playerId, $gold);

        $token = array(
            'type' => 'resurrection',
            'data' => array(
                'army' => Cli_Model_Army::getArmyByArmyId($armyId, $gameId, $db),
                'gold' => $gold
            ),
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);

        return true;
    }
}