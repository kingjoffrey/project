<?php

class Cli_Model_EditorRuin extends Cli_Model_Entity
{
    private $_empty;

    public function __construct($x, $y, $mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($mapId, $db);
        $this->_id = $mMapRuins->add($x, $y);;
        $this->_x = $x;
        $this->_y = $y;
        $this->_empty = false;
    }

    public function toArray()
    {
        return array(
            'empty' => $this->_empty,
            'x' => $this->_x,
            'y' => $this->_y,
        );
    }
}
