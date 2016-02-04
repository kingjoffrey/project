<?php

class Cli_Model_EditorCastle extends Cli_Model_Castle
{
    private $_mapId;

    public function __construct($mapId)
    {
        $this->_mapId = $mapId;
    }

    public function create($x, $y, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_x = $x;
        $this->_y = $y;

        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $this->_id = $mMapCastles->add($this->_x, $this->_y);

//        $mapCastle = $mMapCastles->get();
//        $this->_defense = $mapCastle['defense'];
//        $this->_name = $mapCastle['name'];
//        $this->_income = $mapCastle['income'];
//        $this->_capital = $mapCastle['capital'];
//        $this->_enclaveNumber = $mapCastle['enclaveNumber'];
    }

    public function edit($data, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_defense = $data['defence'];
        $this->_name = $data['name'];

        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mMapCastles->edit($data, $this->_id);
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