<?php

class Application_Model_Castle extends Coret_Db_Table_Abstract
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_sequence = 'castle_castleId_seq';

    public function __construct($db = null)
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
            ->from($this->_name, 'min("castleId")')
            ->where($this->_db->quoteIdentifier('castleId') . ' NOT IN (?)', new Zend_Db_Expr($ids));

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
}

