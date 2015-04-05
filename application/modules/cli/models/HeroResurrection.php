<?php

class Cli_Model_HeroResurrection
{

    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $game = Cli_Model_Game::getGame($user);
        $gameId = $game->getId();
        $color = $user->parameters['me']->getColor();
        $playerId = $user->parameters['me']->getId();
        $player = $game->getPlayers()->getPlayer($color);

        if ($player->getGold() < 100) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($game->getPlayerCapitalId($color))) {
            $gameHandler->sendError($user, 'Aby wskrzesić herosa musisz posiadać stolicę!');
            return;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $hero = $mHeroesInGame->getDeadHero($playerId);

        if (empty($hero)) {
            $gameHandler->sendError($user, 'Twój heros żyje! ');
            return;
        }

        if (!$armyId = $player->getArmies()->getArmyIdFromField($game->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $game, $db);
        }

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($hero['heroId'], new Cli_Model_Hero($hero), $gameId, $db);
        $army->getHeroes()->getHero($hero['heroId'])->zeroMovesLeft($gameId, $db);

        $player->subtractGold(100);
        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'army' => $army->toArray(),
            'gold' => $player->getGold(),
            'color' => $color
        );
        $gameHandler->sendToChannel($game, $token);
    }


}
