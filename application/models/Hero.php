<?php

class Application_Model_Hero extends Coret_Db_Table_Abstract
{
    protected $_name = 'hero';
    protected $_primary = 'heroId';
    protected $_sequence = 'hero_heroId_seq';
    protected $_playerId;

    public function __construct($playerId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_playerId = $playerId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function createHero($name)
    {
        $data = array(
            'playerId' => $this->_playerId,
            'name' => $name
        );

        $this->insert($data);

        return $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
    }

    public function getHeroes()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('heroId', 'numberOfMoves', 'attackPoints', 'defensePoints', 'experience', 'name'))
            ->where('"playerId" = ?', $this->_playerId);

        return $this->selectAll($select);
    }

    public function getHeroesNameExp()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('heroId', 'experience', 'name'))
            ->where('"playerId" = ?', $this->_playerId);

        return $this->selectAll($select);
    }

    public function getHero($id)
    {
        $playerId = $this->_db->quoteIdentifier('playerId');
        $heroId = $this->_db->quoteIdentifier('heroId');

        $select = $this->_db->select()
            ->from($this->_name, array('heroId', 'numberOfMoves', 'attackPoints', 'defensePoints', 'experience', 'name'))
            ->where($heroId . ' = ?', $id)
            ->where($playerId . ' = ?', $this->_playerId);

        return $this->selectRow($select);
    }

    public function getFirstHeroId()
    {
        $playerId = $this->_db->quoteIdentifier('playerId');
        $heroId = $this->_db->quoteIdentifier('heroId');

        $select = $this->_db->select()
            ->from($this->_name, 'min(' . $heroId . ')')
            ->where($playerId . ' = ?', $this->_playerId);

        return $this->selectOne($select);
    }

    public function changeHeroName($heroId, $name)
    {
        $data['name'] = $name;
        $where = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $heroId);
        return $this->update($data, $where);
    }

    public function isMyHero($heroId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'heroId')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId)
            ->where($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId);

        return $this->selectOne($select);
    }

    public function addExperience($heroId, $points)
    {
        $data['experience'] = new Zend_Db_Expr('experience + ' . intval($points));
        $where = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $heroId);
        return $this->update($data, $where);
    }
}

