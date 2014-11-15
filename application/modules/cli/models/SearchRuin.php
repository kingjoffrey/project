<?php

class Cli_Model_SearchRuin
{

    public function __construct($armyId, $user, $db, $gameHandler)
    {
        if (!Zend_Validate::is($armyId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak armii!');
            return;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
        $hero = $mHeroesInGame->getHeroByArmyIdPlayerId($armyId, $user->parameters['playerId']);

        if (empty($hero)) {
            $gameHandler->sendError($user, 'Tylko Heros może przeszukiwać ruiny!');
            return;
        }

        if ($hero['movesLeft'] == 0) {
            $gameHandler->sendError($user, 'Heros ma za mało ruchów!');
            return;
        }

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $position = $mArmy2->getArmyPositionByArmyIdPlayerId($armyId, $user->parameters['playerId']);
        $ruinId = Application_Model_Board::confirmRuinPosition($position);

        if (!Zend_Validate::is($ruinId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak ruin');
            return;
        }

        $mRuinsInGame = new Application_Model_RuinsInGame($user->parameters['gameId'], $db);

        if ($mRuinsInGame->ruinExists($ruinId)) {
            $gameHandler->sendError($user, 'Ruiny są już przeszukane.');
            return;
        }

        $found = self::search($user->parameters['gameId'], $ruinId, $hero['heroId'], $armyId, $user->parameters['playerId'], $db);

        if ($mRuinsInGame->ruinExists($ruinId)) {
            $ruin = array(
                'ruinId' => $ruinId,
                'empty' => 1
            );
        } else {
            $ruin = array(
                'ruinId' => $ruinId,
                'empty' => 0
            );
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'ruin',
            'army' => Cli_Model_Army::getArmyByArmyId($armyId, $user->parameters['gameId'], $db),
            'ruin' => $ruin,
            'find' => $found,
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }
}