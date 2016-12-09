<?php

class Cli_Model_HeroResurrection
{

    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $gameId = $game->getId();
        $color = $user->parameters['me']->getColor();
        $playerId = $user->parameters['me']->getId();
        $player = $game->getPlayers()->getPlayer($color);

        if ($player->getGold() < 100) {
            $handler->sendError($user, 'Za mało złota!');
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($player->getCapitalId())) {
            $handler->sendError($user, 'Aby wskrzesić herosa musisz posiadać stolicę!');
            return;
        }

        $db = $handler->getDb();
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $hero = $mHeroesInGame->getDeadHero($playerId);

        if (empty($hero)) {
            $handler->sendError($user, 'Twój heros żyje! ');
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
        $handler->sendToChannel($token);
    }


}
