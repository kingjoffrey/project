<?php

class Cli_Model_HeroHire
{
    public function __construct(IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $gameId = $game->getId();
        $color = $game->getMe()->getColor();
        $playerId = $game->getMe()->getId();
        $player = $game->getPlayers()->getPlayer($color);

        if ($player->getGold() < 1000) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($game->getPlayerCapitalId($color))) {
            $gameHandler->sendError($user, 'Aby wynająć herosa musisz posiadać stolicę!');
            return;
        }

        $mHero = new Application_Model_Hero($playerId, $db);
        $heroId = $mHero->createHero();

        if (!$armyId = $player->getArmies()->getArmyIdFromPosition($capital->getX(), $capital->getY())) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $game, $db);
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->add($armyId, $heroId);

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($heroId, new Cli_Model_Hero($mHeroesInGame->getHero($heroId)), $gameId, $db);

        $player->subtractGold(1000, $gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'army' => $army->toArray(),
            'gold' => 1000,
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }
}
