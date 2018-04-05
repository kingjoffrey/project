<?php

class Application_Model_HeroesToMapRuins extends Coret_Db_Table_Abstract
{
    protected $_name = 'heroestomapruins';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

//    public function getHeroesMapRuins($gameId, $playerId)
//    {
//        $select = $this->_db->select()
//            ->from(array('a' => $this->_name), '')
//            ->join(array('b' => 'mapruins'), 'a."mapRuinId" = b."mapRuinId"', array('mapRuinId'))
//            ->join(array('c' => 'heroesingame'), 'a."heroId" = c."heroId"', '')
//            ->join(array('d' => 'hero'), 'a."heroId" = d."heroId"', '')
//            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $gameId)
//            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
//            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');
//
//        return $this->selectAll($select);
//    }

    public function getHeroMapRuins($heroId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), '')
            ->join(array('b' => 'mapruins'), 'a."mapRuinId" = b."mapRuinId"', array('mapRuinId'))
            ->join(array('c' => 'ruin'), 'b."ruinId" = c."ruinId"', array('type'))
            ->where($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId)
            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');

        $array = array();

        foreach ($this->selectAll($select) as $key => $row) {
            $array[$row['mapRuinId']] = $row['type'];
        }

        return $array;
    }
}

