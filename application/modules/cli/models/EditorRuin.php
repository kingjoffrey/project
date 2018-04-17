<?php

class Cli_Model_EditorRuin extends Cli_Model_Ruin
{
    public function __construct($ruin, $empty = true)
    {
        if (isset($ruin['mapRuinId'])) {

        }
        parent::__construct($ruin, $empty);
    }

    private function create($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($mapId, $db);
        $this->_id = $mMapRuins->add($this->_x, $this->_y, $this->_ruinId);
    }

    public function delete($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($mapId, $db);

    }

    public function add($id)
    {
        $this->_id = $id;
    }
}
