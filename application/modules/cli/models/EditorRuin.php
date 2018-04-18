<?php

class Cli_Model_EditorRuin extends Cli_Model_Ruin
{
    public function __construct($ruin, $empty = true)
    {
        if (!isset($ruin['mapRuinId'])) {
            $ruin['mapRuinId'] = 0;
            $ruin['ruinId'] = 4;
        }
        parent::__construct($ruin, $empty);
    }

    public function create($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($mapId, $db);
        $this->_id = $mMapRuins->add($this->_x, $this->_y, $this->_type);
    }

    public function delete($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapRuins = new Application_Model_MapRuins($mapId, $db);
        $mMapRuins->remove($this->getId());
    }

//    public function add($id)
//    {
//        $this->_id = $id;
//    }

    public function setType($type, $mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_type != $type) {
            $mMapRuins = new Application_Model_MapRuins($mapId, $db);
            $mMapRuins->setRuinId($this->getId(), $type);
            $this->_type = $type;
        }
    }
}
