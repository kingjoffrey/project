<?php

class Cli_Model_Tower
{
    public $_id;

    public function __construct($tower)
    {
        $this->_id = $tower['towerId'];
    }
}
