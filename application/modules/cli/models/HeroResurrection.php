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
            $l = new Coret_Model_Logger('Cli_Model_HeroResurrection');
            $l->log('Za mało złota!');
            $handler->sendError($user, 'Error 1010');
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($player->getCapitalId())) {
            $l = new Coret_Model_Logger('Cli_Model_HeroResurrection');
            $l->log('Aby wskrzesić herosa musisz posiadać stolicę!');
            $handler->sendError($user, 'Error 1011');
            return;
        }

        $db = $handler->getDb();
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $hero = $mHeroesInGame->getDeadHero($playerId);

        if (empty($hero)) {
            $l = new Coret_Model_Logger('Cli_Model_HeroResurrection');
            $l->log('Twój heros żyje!');
            $handler->sendError($user, 'Error 1012');
            return;
        }

        if (!$armyId = $player->getArmies()->getArmyIdFromField($game->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $game, $db);
        }

        $mHeroSkills = new Application_Model_Heroskills($db);
        $mHeroesToMapRuins = new Application_Model_HeroesToMapRuins($db);

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($hero['heroId'], new Cli_Model_Hero(
            $hero,
            $mHeroSkills->getBonuses($hero['heroId']),
            $mHeroesToMapRuins->getHeroMapRuins($hero['heroId'])
        ), $gameId, $db);
        $army->getHeroes()->getHero($hero['heroId'])->zeroMovesLeft($gameId, $db);

        $gold = 100;

        $player->addGold(-$gold);
        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'army' => $army->toArray(),
            'gold' => $gold,
            'color' => $color
        );
        $handler->sendToChannel($token);
    }


}
