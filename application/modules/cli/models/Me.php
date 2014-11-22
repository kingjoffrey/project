<?php

class Cli_Model_Me
{
    private $_gold;
    private $_accessKey;
    private $_color;
    private $_turn = false;
    private $_team;

    public function __construct($color, $team, $player)
    {
        $this->_gold = $player['gold'];
        $this->_accessKey = $player['accessKey'];
        $this->_color = $color;
        $this->_team = $team;
    }

    public function setTurn($turn)
    {
        $this->_turn = $turn;
    }

    public function toArray()
    {
        return array(
            'gold' => $this->_gold,
            'accessKey' => $this->_accessKey,
            'color' => $this->_color,
            'turn' => $this->_turn
        );
    }

    public function isMyColor($color)
    {
        return $color == $this->_color;
    }

    public function getTeam()
    {
        return $this->_team;
    }

    public function getColor()
    {
        return $this->_color;
    }
}