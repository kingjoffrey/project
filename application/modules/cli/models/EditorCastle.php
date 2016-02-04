<?php

class Cli_Model_EditorCastle extends Cli_Model_Castle
{
    private $_mapId;

    public function __construct()
    {
        $this->_capital = false;
    }

    public function init($castle)
    {
        $this->_x = $castle['x'];
        $this->_y = $castle['y'];
        $this->_id = $castle['mapCastleId'];
        $this->_defense = $castle['defense'];
        $this->_name = $castle['name'];
    }

    public function create($mapId, $x, $y, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_x = $x;
        $this->_y = $y;

        $mMapCastles = new Application_Model_MapCastles($mapId, $db);
        $this->_id = $mMapCastles->add($this->_x, $this->_y);

//        $mapCastle = $mMapCastles->get();
//        $this->_defense = $mapCastle['defense'];
//        $this->_name = $mapCastle['name'];
//        $this->_income = $mapCastle['income'];
//        $this->_capital = $mapCastle['capital'];
//        $this->_enclaveNumber = $mapCastle['enclaveNumber'];
    }

    public function edit($mapId, $data, Zend_Db_Adapter_Pdo_Pgsql $db, $mapPlayerId = 0)
    {
        $this->_defense = $data['defence'];
        $this->_name = $data['name'];
        $this->_income = $data['income'];

        $mMapCastles = new Application_Model_MapCastles($mapId, $db);
        $mMapCastles->edit($this->arrayForDb($mapPlayerId), $this->_id);
    }

    private function arrayForDb($mapPlayerId)
    {
        return array(
            'x' => $this->_x,
            'y' => $this->_y,
            'defense' => $this->_defense,
            'name' => $this->_name,
            'income' => $this->_income,
//            'capital' => $this->_capital,
            'mapPlayerId' => $mapPlayerId,
//            'enclaveNumber' => $this->_enclaveNumber,
        );
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'x' => $this->_x,
            'y' => $this->_y,
            'defense' => $this->_defense,
            'name' => $this->_name,
            'income' => $this->_income,
            'capital' => $this->_capital,
//            'enclaveNumber' => $this->_enclaveNumber,
            'production' => $this->_production
        );
    }

}