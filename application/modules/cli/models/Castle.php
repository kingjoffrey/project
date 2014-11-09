<?php

class Cli_Model_Castle
{
    private $_x;
    private $_y;
    private $_defense;
    private $_name;
    private $_income;
    private $_capital;
    private $_enclaveNumber;

    private $_productionId;
    private $_productionTurn;
    private $_defenseMod;
    private $_relocationCastleId;

    private $_production = array();

    public function __construct($playerCastle, $mapCastle)
    {
        $this->_x = $mapCastle['x'];
        $this->_y = $mapCastle['y'];
        $this->_defense = $mapCastle['defense'];
        $this->_name = $mapCastle['name'];
        $this->_income = $mapCastle['income'];
        $this->_capital = $mapCastle['capital'];
        $this->_enclaveNumber = $mapCastle['enclaveNumber'];

        $this->_productionId = $playerCastle['productionId'];
        $this->_productionTurn = $playerCastle['productionTurn'];
        $this->_defenseMod = $playerCastle['defenseMod'];
        $this->_relocationCastleId = $playerCastle['relocationCastleId'];
    }

    public function setProduction($production)
    {
        $this->_production = $production;
    }

    public function toArray()
    {
        return array(
            'x' => $this->_x,
            'y' => $this->_y,
            'currentProductionId' => $this->_productionId,
            'currentProductionTurn' => $this->_productionTurn,
            'defenseMod' => $this->_defenseMod,
            'relocationCastleId' => $this->_relocationCastleId,
            'defense' => $this->_defense,
            'name' => $this->_name,
            'income' => $this->_income,
            'capital' => $this->_capital,
            'enclaveNumber' => $this->_enclaveNumber,
            'production' => $this->_production
        );
    }

    public function canProduceThisUnit($unitId)
    {
        return isset($this->_production[$unitId]);
    }

    public function getProductionId()
    {
        return $this->_productionId;
    }

    public function setProductionId($gameId, $playerId, $castleId, $unitId, $relocationToCastleId, $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->setProduction($playerId, $castleId, $unitId, $relocationToCastleId);
        $this->_productionId = $unitId;
        $this->_productionTurn = 0;
        $this->_relocationCastleId = $relocationToCastleId;
    }
}