<?php

class Cli_Model_Enemies
{
    private $_enemies = array();

    public function __construct(Cli_Model_Game $game, Cli_Model_Fields $fields, Cli_Model_Players $players, Cli_Model_Path $path, $playerColor)
    {
        if ($castleId = $this->_fields->getCastleId($path->x, $path->y)) {
            $defenderColor = $this->_fields->getCastleColor($path->x, $path->y);
            if ($defenderColor == 'neutral') {
                $this->_enemies = $this->_players->getPlayer($defenderColor)->getCastleGarrison($this->_game->getTurnNumber(), $this->_game->getFirstUnitId());
            } elseif ($this->_players->sameTeam($defenderColor, $playerColor)) {
                $this->_enemies = $this->_game->handleCastleGarrison($this->_players->getPlayer($this->_fields->getCastleColor($path->x, $path->y))->getCastle($castleId));
            }
        } elseif ($enemyArmies = $this->_fields->getArmies($path->x, $path->y)) {
            foreach ($enemyArmies as $armyId => $color) {
                $this->_enemies[] = $this->_players->getPlayer($color)->getArmy($armyId);
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

    public function toArray()
    {
        $enemies = array();
        foreach (array_keys($this->_enemies) as $armyId) {
            $enemies[] = $armyId;
        }
        return $enemies;
    }
}

