<?php

class Cli_Model_EditorCastle extends Cli_Model_Castle
{
    protected $_capital;

    public function __construct($playerCastle, $mapCastle)
    {
        parent::__construct($playerCastle, $mapCastle);

        $this->_capital = $mapCastle['capital'];
    }

    public function initProduction($production)
    {
        foreach ($production as $unitId => $row) {
            $this->_production[] = $row;
        }
    }

    public function edit($mapId, $data, Zend_Db_Adapter_Pdo_Pgsql $db, $sideId = 0)
    {
        $this->_defense = $data['defense'];
//        $this->_name = $data['name'];
//        $this->_income = $data['income'];
        $this->_capital = $data['capital'];
        $this->_enclaveNumber = $data['enclaveNumber'];

        $mMapCastles = new Application_Model_MapCastles($mapId, $db);
        $mMapCastles->edit($this->arrayForDb($sideId), $this->_id);

        $mMapCastleProduction = new Application_Model_MapCastleProduction($db);

        foreach ($data['production'] as $i => $slot) {
            if (isset($this->_production[$i])) {
                if ($slot['unitId']) {
                    if ($this->_production[$i]['unitId'] == $slot['unitId'] && $this->_production[$i]['time'] == $slot['time']) {
                        continue;
                    }
                    $mMapCastleProduction->editCastleProduction($this->_id, $this->_production[$i]['unitId'], $slot);
                    $this->_production[$i] = $slot;
                } else {
                    $mMapCastleProduction->removeCastleProduction($this->_id, $this->_production[$i]['unitId']);
                    unset($this->_production[$i]);
                }

            } else {
                if (isset($slot['unitId']) && $slot['unitId']) {
                    $mMapCastleProduction->addCastleProduction($this->_id, $slot);
                    $this->_production[$i] = $slot;
                }
            }
        }
    }

    public function getCapital()
    {
        return $this->_capital;
    }

    private function arrayForDb($sideId)
    {
        return array(
            'x' => $this->_x,
            'y' => $this->_y,
            'defense' => intval($this->_defense),
//            'name' => $this->_name,
//            'income' => intval($this->_income),
            'capital' => intval($this->_capital),
            'sideId' => $sideId,
            'enclaveNumber' => intval($this->_enclaveNumber),
        );
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'x' => $this->_x,
            'y' => $this->_y,
            'defense' => $this->getDefense(),
            'name' => $this->_name,
            'income' => intval($this->_income),
//            'capital' => $this->_capital,
            'enclaveNumber' => $this->_enclaveNumber,
            'production' => $this->_production
        );
    }

}