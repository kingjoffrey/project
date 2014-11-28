<?php

class Cli_Model_Players
{
    private $_players = array();

    public function get()
    {
        return $this->_players;
    }

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

    public function sameTeam($color1, $color2)
    {
        if ($color1 == $color2) {
            return true;
        }
        return $this->getPlayer($color1)->getTeam() == $this->getPlayer($color2)->getTeam();
    }

    public function noEnemyCastles($playerColor)
    {
        $playerTeam = $this->getPlayer($playerColor)->getTeam();
        foreach ($this->_players as $color => $player) {
            if ($color == $playerColor || $playerTeam == $player->getTeam()) {
                continue;
            }
            if ($player->castlesExists()) {
                return false;
            }
        }
        return true;
    }


}