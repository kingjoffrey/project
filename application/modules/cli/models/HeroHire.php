<?php

class Cli_Model_HeroHire
{
    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $gameId = $game->getId();
        $color = $user->parameters['me']->getColor();
        $playerId = $user->parameters['me']->getId();
        $player = $game->getPlayers()->getPlayer($color);

        if ($player->getGold() < 1000) {
            $l = new Coret_Model_Logger('Cli_Model_HeroHire');
            $l->log('Za mało złota!');
            $handler->sendError($user, 'Error 1008');
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($player->getCapitalId())) {
            $l = new Coret_Model_Logger('Cli_Model_HeroHire');
            $l->log('Aby wynająć herosa musisz posiadać stolicę!');
            $handler->sendError($user, 'Error 1009');
            return;
        }

        $db = $handler->getDb();
        $mHero = new Application_Model_Hero($playerId, $db);
        $heroId = $mHero->createHero();

        if (!$armyId = $player->getArmies()->getArmyIdFromField($game->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $game, $db);
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->add($armyId, $heroId);

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($heroId, new Cli_Model_Hero($mHeroesInGame->getHero($heroId)), $gameId, $db);
        $army->getHeroes()->getHero($heroId)->zeroMovesLeft($gameId, $db);

        $player->subtractGold(1000);
        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'army' => $army->toArray(),
            'gold' => 1000,
            'color' => $color
        );

        $handler->sendToChannel($token);
    }
}
