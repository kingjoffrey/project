<?php

class Cli_Model_Me
{
    private $_gold;
    private $_accessKey;
    private $_color;
    private $_turn = false;
    private $_battleSequence;
    private $_team;

    public function __construct($color, $team, $player, $battleSequence)
    {
        $this->_gold = $player['gold'];
        $this->_accessKey = $player['accessKey'];
        $this->_color = $color;
        $this->setBattleSequence($battleSequence);
        $this->_team = $team;
    }

    public function setBattleSequence($battleSequence)
    {
        $this->_battleSequence = $battleSequence;
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
            'turn' => $this->_turn,
            'battleSequence' => $this->_battleSequence
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
}