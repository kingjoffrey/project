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

    public function add($heroId, $mapRuinId, $gameId)
    {
        $data = array(
            'heroId' => $heroId,
            'mapRuinId' => $mapRuinId,
            'gameId' => $gameId
        );

        return $this->insert($data);
    }

    public function getHeroMapRuins($heroId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), '')
            ->join(array('b' => 'mapruins'), 'a."mapRuinId" = b."mapRuinId"', array('mapRuinId'))
            ->join(array('c' => 'ruin'), 'b."ruinId" = c."ruinId"', array('ruinId'))
            ->where($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId);

        $array = array();

        foreach ($this->selectAll($select) as $key => $row) {
            $array[$row['mapRuinId']] = $row['ruinId'];
        }

        return $array;
    }
}

