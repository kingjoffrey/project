<?php

class Cli_Model_Me
{
    private $_id;
    private $_color;

    public function __construct($color, $playerId)
    {
        $this->_id = $playerId;
    }

    public function toArray()
    {
        return array(
            'color' => $this->_color
        );
    }

    public function getId()
    {
        return $this->_id;
    }
}