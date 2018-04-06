<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_SearchRuinHandler
{

    /**
     * @param int $armyId
     * @param WebSocketTransportInterface $user
     * @param Cli_CommonHandler $handler
     * @param boolean $giveMeDragons
     */
    public function __construct($armyId, WebSocketTransportInterface $user, $handler, $giveMeDragons = false)
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

        if ($ruin->getType() != 4) {
            $l = new Coret_Model_Logger('Cli_Model_SearchRuinHandler');
            $l->log('Tych ruin nie da się przeszukać.');
            $handler->sendError($user, 'Error 10311');
            return;
        }

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

        if ($army->getHeroes()->hasHero($heroId)) {
            $mHero = new Application_Model_Hero(null, $handler->getDb());
            $mHero->addExperience($heroId, 1);
        }
    }
}