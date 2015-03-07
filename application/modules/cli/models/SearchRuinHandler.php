<?php

class Cli_Model_SearchRuinHandler
{

    public function __construct($armyId, IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (!Zend_Validate::is($armyId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak armii!');
            return;
        }

        $playerId = $user->parameters['me']->getId();
        $color = $user->parameters['game']->getPlayerColor($playerId);
        $army = $user->parameters['game']->getPlayers()->getPlayer($color)->getArmies()->getArmy($armyId);

        if (!$ruinId = $user->parameters['game']->getFields()->isRuin($army->getX(), $army->getY())) {
            $gameHandler->sendError($user, 'Brak ruin');
            return;
        }

        $ruin = $user->parameters['game']->getRuins()->getRuin($ruinId);

        if ($ruin->getEmpty()) {
            $gameHandler->sendError($user, 'Ruiny są już przeszukane.');
            return;
        }

        if (!$heroId = $army->getHeroes()->getAnyHeroId()) {
            $gameHandler->sendError($user, 'Tylko Heros może przeszukiwać ruiny!');
            return;
        }

        $hero = $army->getHeroes()->getHero($heroId);

        if ($hero->getMovesLeft() <= 0) {
            $gameHandler->sendError($user, 'Heros ma za mało ruchów!');
            return;
        }

        $ruin->search($user->parameters['game'], $army, $heroId, $playerId, $db, $gameHandler);
    }
}