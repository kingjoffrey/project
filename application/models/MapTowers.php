<?php

class Application_Model_MapTowers extends Coret_Db_Table_Abstract
{
    protected $_name = 'maptowers';
    protected $_primary = 'mapTowerId';
    protected $_sequence = 'maptowers_mapTowerId_seq';
    protected $mapId;

    public function __construct($mapId, $db = null)
    {
        $this->mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getMapTowers()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapTowerId', 'x', 'y'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

        $mapTowers = array();

        foreach ($this->selectAll($select) as $val) {
            $mapTowers[$val['mapTowerId']] = array(
                'x' => $val['x'],
                'y' => $val['y']
            );
        }

        return $mapTowers;
    }

    /**
     * @param $x
     * @param $y
     * @return mixed|string
     * @throws Exception
     */
    public function add($x, $y)
    {
        $data = array(
            'mapId' => $this->mapId,
            'x' => $x,
            'y' => $y
        );
        return $this->insert($data);
    }

    public function remove($id)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId),
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $id)
        );

        $this->delete($where);
    }
}

