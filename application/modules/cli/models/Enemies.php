<?php

class Cli_Model_Enemies
{
    private $_enemies = array();

    public function __construct(Cli_Model_Game $game, $x, $y, $playerColor)
    {
        $fields = $game->getFields();
        $players = $game->getPlayers();
        if ($castleId = $fields->getCastleId($x, $y)) {
            $defenderColor = $fields->getCastleColor($x, $y);
            if ($defenderColor == 'neutral') {
                $this->_enemies = $players->getPlayer($defenderColor)->getCastleGarrison($game->getTurnNumber(), $game->getFirstUnitId(), $castleId);
            } elseif (!$players->sameTeam($defenderColor, $playerColor)) {
                $this->handleCastleGarrison($players->getPlayer($defenderColor)->getCastles()->getCastle($castleId), $fields, $players);
            }
        } elseif ($enemyArmies = $fields->getArmies($x, $y)) {
            foreach ($enemyArmies as $armyId => $color) {
                $this->_enemies[] = $players->getPlayer($color)->getArmies()->getArmy($armyId);
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

    private function handleCastleGarrison(Cli_Model_Castle $castle, Cli_Model_Fields $fields, Cli_Model_Players $players)
    {
        $enemies = array();
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        for ($i = $castleX; $i <= $castleX + 1; $i++) {
            for ($j = $castleY; $j <= $castleY + 1; $j++) {
                foreach ($fields->getArmies($i, $j) as $armyId => $color) {
                    $this->_enemies[] = $players->getPlayer($color)->getArmies()->getArmy($armyId);
                }
            }
        }

        return $enemies;
    }
}

