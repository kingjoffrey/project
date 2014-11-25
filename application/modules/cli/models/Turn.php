<?php

class Cli_Model_Turn
{
    protected $_db;
    protected $_gameHandler;
    protected $_user;
    protected $_game;
    protected $_players;

    public function __construct($user, Cli_Model_Game $game, $db, $gameHandler)
    {
        $this->_user = $user;
        $this->_game = $game;
        $this->_db = $db;
        $this->_gameHandler = $gameHandler;
        $this->_gameId = $this->_game->getId();
        $this->_players = $this->_game->getPlayers();
    }
}
