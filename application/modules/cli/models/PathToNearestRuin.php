<?php

class Cli_Model_PathToNearestRuin
{
    private $_ruinId;
    private $_path;

    public function __construct(Cli_Model_Game $game, Cli_Model_Army $army)
    {
        $armyX = $army->getX();
        $armyY = $army->getY();
        $movesLeft = $army->getMovesLeft();
        foreach ($game->getRuins()->getKeys() as $ruinId) {
            $ruin = $game->getRuins()->getRuin($ruinId);
            if ($ruin->getEmpty()) {
                continue;
            }

            $ruinX = $ruin->getX();
            $ruinY = $ruin->getY();

            $mHeuristics = new Cli_Model_Heuristics($armyX, $armyY);
            $h = $mHeuristics->calculateH($ruinX, $ruinY);
            if ($h < $movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($army, $ruinX, $ruinY, $game);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }
                $this->_path = $aStar->path();
                if ($this->_path && $this->_path->targetWithin()) {
                    $this->_ruinId = $ruinId;
                }
            }
        }
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getRuinId()
    {
        return $this->_ruinId;
    }
}
