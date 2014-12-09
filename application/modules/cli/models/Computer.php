<?php

class Cli_Model_Computer
{
    public function __construct(IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $l = new Coret_Model_Logger();
        $playerId = $game->getTurnPlayerId();
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);

        if (!$player->getTurnActive()) {
            $l->log('START TURY');
            new Cli_Model_StartTurn($playerId, $user, $game, $db, $gameHandler);
            return;
        }

        if (!$player->getComputer()) {
            echo 'To (' . $playerId . ') nie komputer!' . "\n";
//                $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
            return;
        }

        if (Cli_Model_ComputerHeroResurrection::handle($playerId, $game, $db, $gameHandler)) {
            return;
        }

        if ($army = $player->getArmies()->getComputerArmyToMove()) {
            new Cli_Model_ComputerMove($army, $user, $game, $db, $gameHandler);
        } else {
            $l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($game, $db, $gameHandler);
        }
    }
}