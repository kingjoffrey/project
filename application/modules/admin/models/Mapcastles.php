<?php

class Admin_Model_Mapcastles extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_sequence = 'mapcastles_mapCastleId_seq';
    protected $_columns = array(
        'castleId' => array('label' => 'Castle ID', 'type' => 'number'),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
    );

    public function castles($mapId)
    {
        $mCastle = new Admin_Model_Castle();
        $castles = $mCastle->getCastles();

        foreach ($castles as $castle) {
            $data = array(
                'x' => $castle['x'],
//                'y' => $castle['y'] - 79,
                'y' => $castle['y'],
            );

            $where = array(
                $this->_db->quoteInto('"mapId" = ?', $mapId),
                $this->_db->quoteInto('"castleId" = ?', $castle['castleId'])
            );

            $this->update($data, $where);

        }
    }

}

