<?php

class Cli_Model_ComputerHeroResurrection
{
    static public function handle($playerId, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $gameId = $game->getId();
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);
        $gold = $player->getGold();

        if ($gold < 100) {
            return;
        }

        $castleId = $game->getPlayerCapital($color);

        if (!$capital = $player->getCastles()->getCastle($castleId)) {
            return;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $hero = $mHeroesInGame->getDeadHero($playerId);

        if (!$hero) {
            return;
        }

        if ($armyId = $player->getArmyIdFromPosition($capital->getX(), $capital->getY())) {
            $army = $player->getArmies()->getArmy($armyId);
        } else {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $playerId, $game, $db);
        }

        $army->addHero($hero['heroId'], new Cli_Model_Hero($hero), $gameId, $db);

        if (!$armyId) {
            return;
        }

        $l = new Coret_Model_Logger();
        $l->log('WSKRZESZAM HEROSA id = ' . $heroId);

        $player->subtractGold(100, $gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'data' => array(
                'army' => $army->toArray(),
                'gold' => $player->getGold()
            ),
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);

        return true;
    }
}