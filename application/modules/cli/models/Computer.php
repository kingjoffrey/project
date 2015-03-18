<?php

class Cli_Model_Computer
{
    public function __construct(IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $l = new Coret_Model_Logger();
        $game = Cli_Model_Game::getGame($user);
        $playerId = $game->getTurnPlayerId();
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);

        if (!$player->getTurnActive()) {
            $l->log('START TURY');
            new Cli_Model_StartTurn($playerId, $user, $db, $gameHandler);
            return;
        }

        if (!$player->getComputer()) {
            echo 'To (' . $playerId . ') nie komputer!' . "\n";
//                $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
            return;
        }

        new Cli_Model_ComputerHeroResurrection($playerId, $user, $db, $gameHandler);

        if ($army = $player->getArmies()->getComputerArmyToMove()) {
            new Cli_Model_ComputerMove($army, $user, $db, $gameHandler);
        } else {
            $l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($user, $db, $gameHandler);
        }
    }
}