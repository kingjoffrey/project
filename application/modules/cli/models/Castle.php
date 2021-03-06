<?php

class Cli_Model_Castle extends Cli_Model_Entity
{
    protected $_defense;
    protected $_name;
    protected $_income;
    protected $_enclaveNumber;

    private $_productionId;
    private $_productionTurn;
    private $_defenseMod = 0;

    protected $_production = array();

    private $_maxDefense = 4;

    public function __construct($playerCastle, $mapCastle)
    {
        $this->_x = $mapCastle['x'];
        $this->_y = $mapCastle['y'];
        $this->_defense = $mapCastle['defense'];
        $this->_name = $mapCastle['name'];
        $this->_income = $mapCastle['income'];
        $this->_enclaveNumber = $mapCastle['enclaveNumber'];

        if (empty($playerCastle)) {
            $this->_id = $mapCastle['mapCastleId'];
        } else {
            $this->_id = $playerCastle['castleId'];
            $this->_productionId = $playerCastle['productionId'];
            $this->_productionTurn = $playerCastle['productionTurn'];
            $this->_defenseMod = $playerCastle['defenseMod'];
        }
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
            'defense' => $this->getDefense(),
            'name' => $this->_name,
            'income' => $this->_income,
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

    public function setProductionId($unitId = null, $playerId = null, $gameId = null, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            $mCastlesInGame->setProduction($playerId, $this->_id, $unitId);
        }
        $this->_productionId = $unitId;
        $this->_productionTurn = 0;
    }

    public function getIncome()
    {
        return $this->_income * $this->getDefense();
    }

    public function resetProductionTurn($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_productionTurn = 0;
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->resetProductionTurn($this->_id);
    }

    static public function countUnitValue(Cli_Model_Unit $unit, $productionTime)
    {
        return ($unit->getAttackPoints() + $unit->getDefensePoints() + $unit->canFly()) / ($productionTime + $unit->getCost());
    }

    public function findBestCastleProduction()
    {
        $units = Zend_Registry::get('units');

        $value = 0;
        $bestUnitId = null;

        foreach ($this->_production as $unitId => $row) {

            $tmpValue = self::countUnitValue($units->getUnit($unitId), $row['time']);

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

    public function getDefense()
    {
        return $this->_defense + $this->_defenseMod;
    }

    public function getDefenseMod()
    {
        return $this->_defenseMod;
    }

    public function decreaseDefenceMod($playerId, $gameId, $db)
    {
        if ($this->getDefense() > 1) {
            $this->_defenseMod--;
            $this->saveDefenceMod($playerId, $gameId, $db);
        }
    }

    public function increaseDefenceMod($playerId, $gameId, $db)
    {
        if ($this->getDefense() < $this->_maxDefense) {
            $this->_defenseMod++;
            $this->saveDefenceMod($playerId, $gameId, $db);
        }
    }

    private function saveDefenceMod($playerId, $gameId, $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastlesInGame->buildDefense($this->_id, $playerId, $this->_defenseMod);
    }

    public function getEnclaveNumber()
    {
        return $this->_enclaveNumber;
    }
}