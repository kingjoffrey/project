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


    public function allEnemiesAreDead($playerColor)
    {
        $playerTeam = $this->getPlayer($playerColor)->getTeam();
        foreach ($this->_players as $color => $player) {
            if ($color == $playerColor || $playerTeam == $player->getTeam()) {
                continue;
            }
            if ($player->castlesExists() || $player->armiesExists()) {
                return false;
            }
        }
        return true;
    }

    public function getEnemies($playerColor)
    {
        $playerTeam = $this->getPlayer($playerColor)->getTeam();
        $enemies = array();
        foreach ($this->_players as $color => $player) {
            if ($color == $playerColor || $playerTeam == $player->getTeam()) {
                continue;
            }
            $enemies = array_merge($enemies, $player->getArmies()->get());
        }
        return $enemies;
    }

    public function initFields($fields)
    {
        foreach ($this->_players as $color => $player) {
            $player->getArmies()->initFields($fields, $color);
            $player->getCastles()->initFields($fields, $color);
            $player->getTowers()->initFields($fields, $color);
        }
    }


}