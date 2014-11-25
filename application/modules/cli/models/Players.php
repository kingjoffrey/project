<?php

class Cli_Model_Players
{
    private $_players = array();

    public function addPlayer($color, $player)
    {
        $this->_players[$color] = $player;
    }

    /**
     * @param $color
     * @return Cli_Model_Player
     */
    public function getPlayer($color)
    {
        return $this->_players[$color];
    }

    public function toArray()
    {
        $players = array();
        foreach ($this->_players as $color => $player) {
            if ($color == 'neutral') {
                continue;
            }
            $players[$color] = $player->toArray();
        }
        return $players;
    }

    public function hasCastle($color, $castleId)
    {
        return $this->getPlayer($color)->hasCastle($castleId);
    }

    public function canCastleProduceThisUnit($color, $castleId, $unitId)
    {
        return $this->getPlayer($color)->canCastleProduceThisUnit($castleId, $unitId);
    }

    public function getCastleCurrentProductionId($color, $castleId)
    {
        return $this->_players->getPlayer($color)->getCastleCurrentProductionId($castleId);
    }

    public function setProductionId($color, $gameId, $castleId, $unitId, $relocationToCastleId, $db)
    {
        $this->_players->getPlayer($color)->setProduction($gameId, $castleId, $unitId, $relocationToCastleId, $db);
    }

    /**
     * @param $color
     * @param $armyId
     * @return Cli_Model_Army
     */
    public function getPlayerArmy($color, $armyId)
    {
        return $this->_players->getPlayer($color)->getArmy($armyId);
    }

    public function joinArmiesAtPosition($color, $armyId, $db)
    {
        return $this->getPlayer($color)->joinArmiesAtPosition($armyId, $this->_id, $db);
    }

    public function getArmiesFromCastle()
    {

    }

    public function playerArmiesOrCastlesExists($color)
    {
        $player = $this->getPlayer($color);
        return $player->armiesExists() || $player->castlesExists();
    }
}