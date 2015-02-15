<?php

class Cli_Model_NearestWeakerHostileCastle
{
    private $_path = null;

    private $_heuristics = array();
    private $_castles = array();

    public function __construct(Cli_Model_Game $game, $playerColor, Cli_Model_Army $army)
    {
        $this->_l = new Coret_Model_Logger();

        $players = $game->getPlayers();
        $this->initHeuristics($players, $playerColor, $army->getX(), $army->getY());

        $castleId = $this->getCastleId($game, $playerColor, $army);

        if (!$castleId) {
            return;
        }

        $this->_path = $this->path($game, $castleId, $army);
        if ($this->_path && $this->_path->exists()) {
            return;
        }

        while (true) {
            if ($castleId = $this->getCastleId($game, $playerColor, $army)) {
                $this->_path = $this->path($game, $castleId, $army);
                if ($this->_path->exists()) {
                    return;
                }
            }
            return;
        }
    }

    private function initHeuristics(Cli_Model_Players $players, $playerColor, $armyX, $armyY)
    {
        $this->_l->logMethodName();
        foreach ($players->getKeys() as $color) {
            if ($players->sameTeam($playerColor, $color)) {
                continue;
            }
            foreach ($players->getPlayer($color)->getCastles()->getKeys() as $castleId) {
                $castle = $players->getPlayer($color)->getCastles()->getCastle($castleId);
                $mHeuristics = new Cli_Model_Heuristics($castle->getX(), $castle->getY());
                $this->_heuristics[$castleId] = $mHeuristics->calculateH($armyX, $armyY);
                $this->_castles[$castleId] = $castle;
            }
        }

        asort($this->_heuristics, SORT_NUMERIC);

        $this->_heuristics = array_keys($this->_heuristics);
    }

    private function getCastleId(Cli_Model_Game $game, $playerColor, $army)
    {
        $this->_l->logMethodName();
        foreach ($this->_heuristics as $k => $castleId) {
            $castle = $this->_castles[$castleId];
            $es = new Cli_Model_EnemyStronger($army, $game, $castle->getX(), $castle->getY(), $playerColor);
            if (!$es->stronger()) {
                return $castleId;
            } else {
                unset($this->_heuristics[$k]);
            }
        }
    }

    private function path(Cli_Model_Game $game, $castleId, Cli_Model_Army $army)
    {
        $this->_l->logMethodName();
        $castle = $this->_castles[$castleId];
        $castleX = $castle->getX();
        $castleY = $castle->getY();
        try {
            $aStar = new Cli_Model_Astar($army, $castleX, $castleY, $game);
        } catch (Exception $e) {
            echo($e);
            return;
        }
        return $aStar->path();
    }

    public function getPath()
    {
        return $this->_path;
    }
}