<?php

class Cli_Model_Me
{
    private $_gold;
    private $_accessKey;
    private $_color;

    public function __construct($color, $player)
    {
        $this->_gold = $player['gold'];
        $this->_accessKey = $player['accessKey'];
        $this->_color = $color;
    }

    public function toArray()
    {
        return array(
            'gold' => $this->_gold,
            'accessKey' => $this->_accessKey,
            'color' => $this->_color,
        );
    }
}