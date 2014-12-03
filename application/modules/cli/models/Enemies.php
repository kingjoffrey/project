<?php

class Cli_Model_Enemies
{
    private $_enemies = array();

    public function __construct(Cli_Model_Game $game, Cli_Model_Fields $fields, Cli_Model_Players $players, Cli_Model_Path $path, $playerColor)
    {
        $pathX = $path->getX();
        $pathY = $path->getY();
        
        if ($castleId = $fields->getCastleId($pathX, $pathY)) {
            $defenderColor = $fields->getCastleColor($pathX, $pathY);
            if ($defenderColor == 'neutral') {
                $this->_enemies = $players->getPlayer($defenderColor)->getCastleGarrison($game->getTurnNumber(), $game->getFirstUnitId(),$castleId);
            } elseif ($players->sameTeam($defenderColor, $playerColor)) {
                $this->_enemies = $game->handleCastleGarrison($players->getPlayer($fields->getCastleColor($pathX, $pathY))->getCastle($castleId));
            }
        } elseif ($enemyArmies = $fields->getArmies($pathX, $pathY)) {
            foreach ($enemyArmies as $armyId => $color) {
                $this->_enemies[] = $players->getPlayer($color)->getArmy($armyId);
            }
        }
    }

    public function get()
    {
        return $this->_enemies;
    }

    public function hasEnemies()
    {
        return count($this->_enemies);
    }
}

