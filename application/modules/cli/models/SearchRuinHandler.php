<?php

class Cli_Model_SearchRuinHandler
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler, $giveMeDragons = false)
    {
        if (!Zend_Validate::is($armyId, 'Digits')) {
            $l = new Coret_Model_Logger('Cli_Model_SearchRuinHandler');
            $l->log('Brak armii!');
            $handler->sendError($user, 'Error 1029');
            return;
        }

        $playerId = $user->parameters['me']->getId();
        $game = Cli_CommonHandler::getGameFromUser($user);
        $color = $game->getPlayerColor($playerId);
        $army = $game->getPlayers()->getPlayer($color)->getArmies()->getArmy($armyId);

        if (!$ruinId = $game->getFields()->getField($army->getX(), $army->getY())->getRuinId()) {
            $l = new Coret_Model_Logger('Cli_Model_SearchRuinHandler');
            $l->log('Brak ruin!');
            $handler->sendError($user, 'Error 1030');
            return;
        }

        $ruin = $game->getRuins()->getRuin($ruinId);

        if ($ruin->getEmpty()) {
            $l = new Coret_Model_Logger('Cli_Model_SearchRuinHandler');
            $l->log('Ruiny są już przeszukane.');
            $handler->sendError($user, 'Error 1031');
            return;
        }

        if (!$heroId = $army->getHeroes()->getAnyHeroId()) {
            $l = new Coret_Model_Logger('Cli_Model_SearchRuinHandler');
            $l->log('Tylko Heros może przeszukiwać ruiny!');
            $handler->sendError($user, 'Error 1032');
            return;
        }

        $hero = $army->getHeroes()->getHero($heroId);

        if ($hero->getMovesLeft() <= 0) {
            $l = new Coret_Model_Logger('Cli_Model_SearchRuinHandler');
            $l->log('Heros ma za mało ruchów!');
            $handler->sendError($user, 'Error 1033');
            return;
        }

        if ($giveMeDragons) {
            $ruin->giveMeDragons($game, $army, $heroId, $playerId, $handler);
        } else {
            $ruin->search($game, $army, $heroId, $playerId, $handler);
        }
    }
}