<?php

class Cli_Model_SearchRuinHandler
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        if (!Zend_Validate::is($armyId, 'Digits')) {
            $handler->sendError($user, 'Brak armii!');
            return;
        }

        $playerId = $user->parameters['me']->getId();
        $game = Cli_CommonHandler::getGameFromUser($user);
        $color = $game->getPlayerColor($playerId);
        $army = $game->getPlayers()->getPlayer($color)->getArmies()->getArmy($armyId);

        if (!$ruinId = $game->getFields()->getField($army->getX(), $army->getY())->getRuinId()) {
            $handler->sendError($user, 'Brak ruin');
            return;
        }

        $ruin = $game->getRuins()->getRuin($ruinId);

        if ($ruin->getEmpty()) {
            $handler->sendError($user, 'Ruiny są już przeszukane.');
            return;
        }

        if (!$heroId = $army->getHeroes()->getAnyHeroId()) {
            $handler->sendError($user, 'Tylko Heros może przeszukiwać ruiny!');
            return;
        }

        $hero = $army->getHeroes()->getHero($heroId);

        if ($hero->getMovesLeft() <= 0) {
            $handler->sendError($user, 'Heros ma za mało ruchów!');
            return;
        }

        $ruin->search($game, $army, $heroId, $playerId, $handler);
    }
}