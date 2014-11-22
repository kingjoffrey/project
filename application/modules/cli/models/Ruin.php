<?php

class Cli_Model_Ruin
{
    private $_id;
    private $_x;
    private $_y;
    private $_empty;

    public function __construct($ruin, $empty)
    {
        $this->_id = $ruin['ruinId'];
        $this->_x = $ruin['x'];
        $this->_y = $ruin['y'];
        $this->_empty = $empty;
    }

    public function toArray()
    {
        return array(
            'empty' => $this->_empty,
            'x' => $this->_x,
            'y' => $this->_y,
        );
    }

    public function getEmpty()
    {
        return $this->_empty;
    }

    public function getX()
    {
        return $this->_x;
    }

    public function getY()
    {
        return $this->_y;
    }

    public function setEmpty($gameId, $db)
    {
        $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
        $mRuinsInGame->add($this->_id);
        $this->_empty = true;
    }
}
