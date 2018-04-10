<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_Computer
{
    public function __construct(WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $playerId = $game->getTurnPlayerId();
        $player = $game->getPlayers()->getPlayer($game->getPlayerColor($playerId));

        if (!$player->getTurnActive()) {
            $l = new Coret_Model_Logger();
            $l->log('***');
            $l->log('START TURY');
            new Cli_Model_StartTurn($playerId, $user, $handler);
            return;
        }

        if (!$player->getComputer()) {
            $l = new Coret_Model_Logger();
            $l->log('To (' . $playerId . ') nie komputer!');
//            echo 'To (' . $playerId . ') nie komputer!' . "\n";
//            $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
            return;
        }

        $CHR = new Cli_Model_ComputerHeroResurrection($playerId, $user, $handler);

        if ($player->getGold() > 1000 && !$CHR->justRessurected()) {
            $l = new Coret_Model_Logger();
            $l->log('HERO HIRE');
            new Cli_Model_HeroHire($playerId, $user, $handler);
        }

        if ($army = $player->getArmies()->getComputerArmyToMove()) {
            new Cli_Model_ComputerMove($army, $user, $handler);
        } else {
            $l = new Coret_Model_Logger();
            $l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($user, $handler);
        }
    }
}