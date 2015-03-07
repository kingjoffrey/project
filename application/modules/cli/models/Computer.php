<?php

class Cli_Model_Computer
{
    public function __construct(IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $l = new Coret_Model_Logger();
        $playerId = $user->parameters['game']->getTurnPlayerId();
        $players = $user->parameters['game']->getPlayers();
        $color = $user->parameters['game']->getPlayerColor($playerId);
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

        if (Cli_Model_ComputerHeroResurrection::handle($playerId, $user, $db, $gameHandler)) {
            return;
        }

        if ($army = $player->getArmies()->getComputerArmyToMove()) {
            new Cli_Model_ComputerMove($army, $user, $db, $gameHandler);
        } else {
            $l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($user->parameters['game'], $db, $gameHandler);
        }
    }
}