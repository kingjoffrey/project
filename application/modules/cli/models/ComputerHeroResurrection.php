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
        $heroId = $mHeroesInGame->getDeadHeroId($playerId);

        if (!$heroId) {
            return;
        }

        if ($armyId = $player->getArmyIdFromPosition($capital->getX(), $capital->getY())) {
            $army = $player->getArmies()->getArmy($armyId);
        } else {
            $mArmy = new Application_Model_Army($gameId, $db);
            $armyId = $mArmy->createArmy($capital->getPosition(), $playerId);
            $army = new Cli_Model_Army(array(
                'armyId' => $armyId,
                'x' => $capital->getX(),
                'y' => $capital->getY()
            ), $color);
            $player->getArmies()->addArmy($armyId, $army);
        }
        $army->createHero($gameId, $heroId, $db);

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