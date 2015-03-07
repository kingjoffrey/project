<?php

class Cli_Model_HeroHire
{
    public function __construct(IWebSocketConnection $user, Cli_Model_Me $me, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $gameId = $user->parameters['game']->getId();
        $color = $me->getColor();
        $playerId = $me->getId();
        $player = $user->parameters['game']->getPlayers()->getPlayer($color);

        if ($player->getGold() < 1000) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($user->parameters['game']->getPlayerCapitalId($color))) {
            $gameHandler->sendError($user, 'Aby wynająć herosa musisz posiadać stolicę!');
            return;
        }

        $mHero = new Application_Model_Hero($playerId, $db);
        $heroId = $mHero->createHero();

        if (!$armyId = $player->getArmies()->getArmyIdFromField($user->parameters['game']->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $user->parameters['game'], $db);
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->add($armyId, $heroId);

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($heroId, new Cli_Model_Hero($mHeroesInGame->getHero($heroId)), $gameId, $db);

        $player->subtractGold(1000);
        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'army' => $army->toArray(),
            'gold' => 1000,
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }
}
