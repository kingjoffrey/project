<?php

class Cli_Model_Me
{
    private $_gold;
    private $_accessKey;
    private $_color;

    public function __construct($playerId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $player = $mPlayersInGame->getGamePlayers();

        $this->_gold = $player['gold'];
        $this->_accessKey = $player['accessKey'];
        $this->_color = $player['color'];
    }
}