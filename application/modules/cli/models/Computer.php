<?php

class Cli_Model_Computer
{
    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        $l = new Coret_Model_Logger();
        $game = Cli_Model_Game::getGame($user);
        $playerId = $game->getTurnPlayerId();
        $player = $game->getPlayers()->getPlayer($game->getPlayerColor($playerId));

        $db = $handler->getDb();
        if (!$player->getTurnActive()) {
            $l->log('START TURY');
            new Cli_Model_StartTurn($playerId, $user, $db, $handler);
            return;
        }

        if (!$player->getComputer()) {
//            echo 'To (' . $playerId . ') nie komputer!' . "\n";
//            $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
            return;
        }

        new Cli_Model_ComputerHeroResurrection($playerId, $user, $db, $handler);

        if ($army = $player->getArmies()->getComputerArmyToMove()) {
            new Cli_Model_ComputerMove($army, $user, $db, $handler);
        } else {
            $l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($user, $db, $handler);
        }
    }
}