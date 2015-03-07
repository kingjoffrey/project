<?php

class Cli_Model_ComputerHeroResurrection
{
    static public function handle($playerId, IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $gameId = $user->parameters['game']->getId();
        $players = $user->parameters['game']->getPlayers();
        $color = $user->parameters['game']->getPlayerColor($playerId);
        $player = $players->getPlayer($color);

        if ($player->getGold() < 100) {
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($user->parameters['game']->getPlayerCapitalId($color))) {
            return;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $hero = $mHeroesInGame->getDeadHero($playerId);

        if (empty($hero)) {
            return;
        }

        if (!$armyId = $player->getArmies()->getArmyIdFromField($user->parameters['game']->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $user->parameters['game'], $db);
        }

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($hero['heroId'], new Cli_Model_Hero($hero), $gameId, $db);
        $army->zeroHeroMovesLeft($hero['heroId'], $gameId, $db);

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

        $gameHandler->sendToChannel($db, $token, $gameId);

        return true;
    }
}