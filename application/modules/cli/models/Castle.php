<?php

class Cli_Model_Castle extends Cli_Model_Entity
{
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

        if (empty($playerCastle)) {
            $this->_id = $mapCastle['castleId'];
            return;
        }

        $this->_id = $playerCastle['castleId'];
        $this->_productionId = $playerCastle['productionId'];
        $this->_productionTurn = $playerCastle['productionTurn'];
        $this->_defenseMod = $playerCastle['defenseMod'];
        $this->_relocationCastleId = $playerCastle['relocationCastleId'];
    }

    public function initProduction($production)
    {
        $this->_production = $production;
    }

    public function getProduction()
    {
        return $this->_production;
    }

    public function getPosition()
    {
        return array('x' => $this->_x, 'y' => $this->_y);
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'x' => $this->_x,
            'y' => $this->_y,
            'productionId' => $this->_productionId,
            'productionTurn' => $this->_productionTurn,
            'relocationCastleId' => $this->_relocationCastleId,
            'defense' => $this->getDefenseModifier(),
            'name' => $this->_name,
            'income' => $this->_income,
            'capital' => $this->_capital,
//            'enclaveNumber' => $this->_enclaveNumber,
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

    public function getProductionTurn()
    {
        return $this->_productionTurn;
    }

    public function incrementProductionTurn()
    {
        return $this->_productionTurn++;
    }

    public function setProductionId($gameId, $playerId, $unitId, $relocationToCastleId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->setProduction($playerId, $this->_id, $unitId, $relocationToCastleId);
        $this->_productionId = $unitId;
        $this->_productionTurn = 0;
        $this->_relocationCastleId = $relocationToCastleId;
    }

    public function getIncome()
    {
        return $this->_income;
    }

    public function resetProductionTurn($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_productionTurn = 0;
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->resetProductionTurn($this->_id);
    }

    public function cancelProductionRelocation($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_relocationCastleId = null;
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->cancelProductionRelocation($this->_id);
    }

    static public function countUnitValue($unit, $productionTime)
    {
        return ($unit['attackPoints'] + $unit['defensePoints'] + $unit['canFly']) / ($productionTime + $unit['cost']);
    }

    public function findBestCastleProduction()
    {
        $units = Zend_Registry::get('units');

        $value = 0;
        $bestUnitId = null;

        foreach ($this->_production as $unitId => $row) {

            $tmpValue = self::countUnitValue($units[$unitId], $row['time']);

            if ($tmpValue > $value) {
                $value = $tmpValue;
                $bestUnitId = $unitId;
            }
        }

        return $bestUnitId;
    }

    public function getUnitIdWithShortestProductionTime()
    {
        $min = 100;
        foreach ($this->_production as $key => $val) {
            if ($val['time'] < $min) {
                $min = $val['time'];
                $unitId = $key;
            }
        }
        return $unitId;
    }

    public function getDefenseModifier()
    {
        return $this->_defense + $this->_defenseMod;
    }

    public function getDefenseMod()
    {
        return $this->_defenseMod;
    }

    public function decreaseDefenceMod($playerId, $gameId, $db)
    {
        if ($this->getDefenseModifier() > 1) {
            $this->_defenseMod--;
            $this->setDefenceMod($playerId, $gameId, $db);
        }
    }

    public function increaseDefenceMod($playerId, $gameId, $db)
    {
        if ($this->getDefenseModifier() < 4) {
            $this->_defenseMod++;
            $this->setDefenceMod($playerId, $gameId, $db);
        }
    }

    private function setDefenceMod($playerId, $gameId, $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->buildDefense($this->_id, $playerId, $this->_defenseMod);
    }

    public function getRelocationCastleId()
    {
        return $this->_relocationCastleId;
    }
}