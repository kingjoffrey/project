<?php

class Application_Model_CastlesInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'castlesingame';
    protected $_primary = array('castleId', 'gameId');
    protected $_sequence = '';
    protected $_castleId;
    protected $_gameId;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function cancelProductionRelocation($castleId)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId)
        );

        $data = array(
            'relocationCastleId' => null
        );

        return $this->update($data, $where);
    }

    public function setProduction($playerId, $castleId, $unitId, $relocationToCastleId = null)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );

        $data = array(
            'productionId' => $unitId,
            'productionTurn' => 0,
            'relocationCastleId' => $relocationToCastleId
        );

        return $this->update($data, $where);
    }

    public function razeCastle($castleId, $playerId)
    {
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($this->_gameId, $this->_db);
        $mCastlesDestroyed->add($castleId, $playerId);

        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );

        $data = array(
            'razed' => 'true',
            'productionId' => null,
            'productionTurn' => 0,
        );

        return $this->update($data, $where);
    }

    public function getPlayerCastles($playerId)
    {
        $playersCastles = array();

        $select = $this->_db->select()
            ->from($this->_name, array('productionId', 'productionTurn', 'defenseMod', 'castleId', 'relocationCastleId'))
            ->where('"playerId" = ?', $playerId)
            ->where('"gameId" = ?', $this->_gameId)
            ->where('razed = false');

        foreach ($this->selectAll($select) as $val) {
            $playersCastles[$val['castleId']] = $val;
        }

        return $playersCastles;
    }

    public function buildDefense($castleId, $playerId, $defenseMod)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"playerId" = ?', $playerId),
            $this->_db->quoteInto('"castleId" = ?', $castleId)
        );

        $data = array(
            'defenseMod' => $defenseMod
        );

        return $this->update($data, $where);
    }

    public function changeOwner(Cli_Model_Castle $castle, $playerId)
    {
        $castleId = $castle->getId();
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"castleId" = ?', $castleId);

        $mCastlesConquered = new Application_Model_CastlesConquered($this->_gameId, $this->_db);
        $mCastlesConquered->add($castleId, $playerId, new Zend_Db_Expr('(' . $select->__toString() . ')'));

        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId)
        );

        $data = array(
            'defenseMod' => $castle->getDefenseMod(),
            'playerId' => $playerId,
            'productionId' => null,
            'productionTurn' => 0,
        );
        print_r($data);
        if(!$this->update($data, $where)){
            exit;
        }
    }

    public function addCastle($castleId, $playerId)
    {
        $mCastlesConquered = new Application_Model_CastlesConquered($this->_gameId, $this->_db);
        $mCastlesConquered->add($castleId, $playerId, 0);

        $data = array(
            'castleId' => $castleId,
            'playerId' => $playerId,
            'gameId' => $this->_gameId
        );

        $this->insert($data);
    }

    public function resetProductionTurn($castleId)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'productionTurn' => 0
        );

        return $this->update($data, $where);
    }

    public function getAllCastles()
    {
        $castles = array();

        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"gameId" = ?', $this->_gameId);
        foreach ($this->_db->query($select)->fetchAll() as $val) {
            $castles[$val['castleId']] = $val;
        }

        return $castles;
    }

    public function increaseAllCastlesProductionTurn($playerId)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );
        $data = array(
            'productionTurn' => new Zend_Db_Expr('"productionTurn" + 1')
        );

        return $this->update($data, $where);
    }
}

