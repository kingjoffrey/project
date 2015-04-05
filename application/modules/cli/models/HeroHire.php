<?php

class Cli_Model_HeroHire
{
    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $game = Cli_Model_Game::getGame($user);
        $gameId = $game->getId();
        $color = $user->parameters['me']->getColor();
        $playerId = $user->parameters['me']->getId();
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

        $gameHandler->sendToChannel($game, $token);
    }
}
