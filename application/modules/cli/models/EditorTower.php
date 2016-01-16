<?php

class Cli_Model_EditorTower extends Cli_Model_Tower
{
    public function __construct($x, $y)
    {
        $this->_x = $x;
        $this->_y = $y;
    }

    public function create($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapTowers = new Application_Model_MapTowers($mapId, $db);
        $this->_id = $mMapTowers->add($this->_x, $this->_y);
    }
}
