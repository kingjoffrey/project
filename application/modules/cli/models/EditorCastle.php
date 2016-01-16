<?php

class Cli_Model_EditorCastle extends Cli_Model_Castle
{
    public function __construct($x, $y)
    {
        $this->_x = $x;
        $this->_y = $y;
    }

    public function create($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapCastles = new Application_Model_MapCastles($mapId, $db);
        $this->_id = $mMapCastles->add($this->_x, $this->_y);
        $mapCastle = $mMapCastles->get();

        $this->_defense = $mapCastle['defense'];
        $this->_name = $mapCastle['name'];
        $this->_income = $mapCastle['income'];
        $this->_capital = $mapCastle['capital'];
        $this->_enclaveNumber = $mapCastle['enclaveNumber'];
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