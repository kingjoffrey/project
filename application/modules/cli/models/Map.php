<?php

class Cli_Model_Map
{
    private $_map;

    public function __construct()
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);

        $this->_map = Application_Model_Board::prepareCastlesAndFields(
            Cli_Model_Army::getEnemyArmiesFieldsPositions($this->_gameId, $this->_db, $this->_playerId),
            $mCastlesInGame->getRazedCastles(),
            $mCastlesInGame->getPlayerCastles($this->_playerId),
            $mCastlesInGame->getTeamCastles($this->_playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($this->_playerId))
        );
    }

    public function getMap()
    {
        return $this->_map;
    }
}
