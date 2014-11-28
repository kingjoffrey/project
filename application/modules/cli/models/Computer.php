<?php

class Cli_Model_Computer
{
    public function __construct(IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $l = new Coret_Model_Logger();
        $playerId = $game->getTurnPlayerId();
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);

        if (!$this->_player->getTurnActive()) {
            $l->log('START TURY');
            $mTurn = new Cli_Model_Turn($user, $game, $db, $gameHandler);
            $mTurn->start($playerId, true);
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

        if ($army = $player->getComputerArmyToMove()) {
            $computer = new Cli_Model_ComputerMove($army, $user, $game, $db, $gameHandler);
            $computer->move();
        } else {
            $l->log('NASTĘPNA TURA');
            $mTurn = new Cli_Model_Turn($user, $game, $db, $gameHandler);
            $mTurn->next($playerId);
        }
    }
}