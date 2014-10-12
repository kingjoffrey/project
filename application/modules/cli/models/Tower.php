<?php

class Cli_Model_Tower
{

    public function __construct($towerId, $user, $db, $gameHandler)
    {
        if ($towerId === null) {
            $gameHandler->sendError($user, 'No "towerId"!');
            return;
        }
        $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
        $playerId = $mGame->getTurnPlayerId();

        $towers = Zend_Registry::get('towers');

        $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);
        if (!$mArmy->isPlayerArmyNearTowerPosition($towers[$towerId], $playerId)) {
            $gameHandler->sendError($user, 'No player army near tower!');
            return;
        }

        if ($mArmy->isAnyArmyAtPosition($towers[$towerId])) {
            $gameHandler->sendError($user, 'There is an army at tower position');
            return;
        }

        $mTowersInGame = new Application_Model_TowersInGame($user->parameters['gameId'], $db);
        if ($towerOwnerId = $mTowersInGame->getTowerOwnerId($towerId)) {
            $teams = Zend_Registry::get('teams');
            if ($teams[$towerOwnerId] == $teams[$playerId]) {
                $gameHandler->sendError($user, 'Your team');
                return;
            }
            $mTowersInGame->changeTowerOwner($towerId, $playerId);
        } else {
            $mTowersInGame->addTower($towerId, $playerId);
        }
    }

}