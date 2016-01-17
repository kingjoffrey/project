<?php

class Application_Model_Castle extends Coret_Db_Table_Abstract
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_sequence = 'castle_castleId_seq';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getNextFreeCastleId($ids)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'min("castleId")');
        if ($ids) {
            $select->where($this->_db->quoteIdentifier('castleId') . ' NOT IN (?)', new Zend_Db_Expr($ids));
        }

        return $this->selectOne($select);
    }

    public function add($name, $income, $defense)
    {
        $data = array(
            'name' => $name,
            'income' => $income,
            'defense' => $defense
        );

        $this->insert($data);
    }

    public function getAll()
    {
        $select = $this->_db->select()
            ->from($this->_name, array($this->_primary, 'name'));

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row[$this->_primary]] = $row['name'];
        }

        return $array;
    }

    public function get()
    {
        $select = $this->_db->select()
            ->from($this->_name, '*');

        return $this->selectAll($select);
    }
}

