<?php

class Cli_Model_StartTurn extends Cli_Model_Turn
{

    public function __construct($playerId, $user, Cli_Model_Game $game, $db, $gameHandler)
    {
        parent::__construct($user, $game, $db, $gameHandler);

        $player = $this->_players->getPlayer($this->_game->getPlayerColor($playerId));
        $this->_game->activatePlayerTurn($playerId, $this->_db);

        if ($player->getComputer()) {
            $player->unfortifyArmies($this->_gameId, $this->_db);
            $type = 'computerStart';
        } else {
            $type = 'startTurn';
        }

        $player->startTurn($this->_gameId, $this->_game->getTurnNumber(), $this->_db);

        $token = array(
            'type' => $type,
            'gold' => $player->getGold(),
            'armies' => $player->armiesToArray(),
            'castles' => $player->castlesToArray(),
            'color' => $this->_game->getPlayerColor($playerId)
        );
        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
    }

}
