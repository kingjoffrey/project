<?php

class Cli_Model_ComputerHeroResurrection
{
    public function __construct($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $gameId = $game->getId();
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);

        if ($player->getGold() < 100) {
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($player->getCapitalId())) {
            return;
        }

        $db = $handler->getDb();
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $hero = $mHeroesInGame->getDeadHero($playerId);

        if (empty($hero)) {
            return;
        }

        if (!$armyId = $player->getArmies()->getArmyIdFromField($game->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $game, $db);
        }

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($hero['heroId'], new Cli_Model_Hero($hero), $gameId, $db);
        $army->getHeroes()->getHero($hero['heroId'])->zeroMovesLeft($gameId, $db);

        $l = new Coret_Model_Logger();
        $l->log('WSKRZESZAM HEROSA id = ' . $hero['heroId']);

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