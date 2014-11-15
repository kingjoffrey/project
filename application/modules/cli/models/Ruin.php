<?php

class Cli_Model_Ruin
{
    private $_empty;
    private $_x;
    private $_y;

    public function __construct($position, $empty)
    {
        $this->_empty = $empty;
        $this->_x = $position['x'];
        $this->_y = $position['y'];
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
