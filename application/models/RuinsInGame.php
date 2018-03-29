<?php

class Application_Model_RuinsInGame extends Coret_Db_Table_Abstract
{

    protected $_name = 'ruinsingame';
    protected $_foreign_1 = 'gameId';
    protected $_foreign_2 = 'mapRuinId';

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getVisited()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_foreign_2)
            ->where($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $this->_gameId);

        $result = $this->selectAll($select);

        $array = array();

        foreach ($result as $row) {
            $array[$row[$this->_foreign_2]] = true;
        }

        return $array;
    }

    public function add($mapRuinId)
    {
        $data = array(
            $this->_foreign_2 => $mapRuinId,
            $this->_foreign_1 => $this->_gameId
        );

        return $this->insert($data);
    }
}

