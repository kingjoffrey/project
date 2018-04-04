<?php

class Application_Model_HeroesInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'heroesingame';
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

    public function add($armyId, $heroId)
    {
        $data = array(
            'heroId' => $heroId,
            'armyId' => $armyId,
            'gameId' => $this->_gameId
        );

        return $this->insert($data);
    }

    public function addToArmy($armyId, $heroId, $movesLeft)
    {
        $data = array(
            'armyId' => $armyId,
            'movesLeft' => $movesLeft
        );
        $where = array(
            $this->_db->quoteInto('"heroId" = ?', $heroId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }

    public function getForMove($armyId)
    {
        $select = $this->_db->select()
            ->from(array('a' => 'hero'), array('numberOfMoves', 'attackPoints', 'defensePoints', 'lifePoints', 'regenerationSpeed', 'name'))
            ->join(array('b' => $this->_name), 'a."heroId" = b."heroId"', array('heroId', 'movesLeft', 'remainingLife', 'attackBonus', 'defenseBonus'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('armyId') . ' = ?', $armyId)
            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');

        return $this->selectAll($select);
    }

    public function updateMovesLeft($movesLeft, $heroId)
    {
        $data = array(
            'movesLeft' => $movesLeft
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->update($data, $where);
    }

    public function updateRemainingLife($remainingLife, $heroId)
    {
        $data = array(
            'remainingLife' => $remainingLife
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->update($data, $where);
    }

    public function armyRemoveHero($heroId)
    {
        $data = array(
            'armyId' => null
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
        );

        return $this->update($data, $where);
    }

    public function heroesUpdateArmyId($oldArmyId, $newArmyId)
    {
        $data = array(
            'armyId' => $newArmyId
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('armyId') . ' = ?', $oldArmyId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }

    public function getDeadHero($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'armyId')
            ->join(array('b' => 'hero'), 'a."heroId" = b."heroId"', null)
            ->join(array('c' => 'army'), 'a."armyId" = c."armyId"', null)
            ->where('a.' . $this->_db->quoteIdentifier('armyId') . ' IS NOT NULL')
            ->where('destroyed = false')
            ->where('a.' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('b.' . $this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        if ($this->selectOne($select)) {
            return;
        }

        $select = $this->_db->select()
            ->from(array('a' => 'hero'), array('numberOfMoves', 'attackPoints', 'defensePoints', 'lifePoints', 'regenerationSpeed', 'name'))
            ->join(array('b' => $this->_name), 'a."heroId" = b."heroId"', array('heroId', 'movesLeft', 'remainingLife', 'attackBonus', 'defenseBonus'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);
        return $this->selectRow($select);
    }

    public function getHero($heroId)
    {
        $select = $this->_db->select()
            ->from(array('a' => 'hero'), array('numberOfMoves', 'attackPoints', 'defensePoints', 'lifePoints', 'regenerationSpeed', 'name'))
            ->join(array('b' => $this->_name), 'a."heroId" = b."heroId"', array('heroId', 'movesLeft', 'remainingLife', 'attackBonus', 'defenseBonus'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('a."heroId" = ?', $heroId);
        return $this->selectRow($select);
    }
}

