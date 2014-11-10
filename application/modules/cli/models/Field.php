<?php

class Cli_Model_Field
{
    private $_type;
    private $_possessionId;
    private $_possessionType;
    private $_possessionColor;

    public function __construct($type)
    {
        $this->_type = $type;
    }

    public function setPossession($id, $type, $color)
    {
        $this->_possessionId = $id;
        $this->_possessionType = $type;
        $this->_possessionColor = $color;
    }
}