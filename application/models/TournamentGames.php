<?php

class Application_Model_TournamentGames extends Coret_Db_Table_Abstract
{
    protected $_name = 'tournamentgames';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function addGame($tournamentId, $gameId)
    {
        $data = array(
            'tournamentId' => $tournamentId,
            'gameId' => $gameId
        );

        return $this->_db->insert($this->_name, $data);
    }

    public function getGameId($tournamentId, $playerId)
    {
        $gId = $this->_db->quoteIdentifier('gameId');
        $pId = $this->_db->quoteIdentifier('playerId');
        $tId = $this->_db->quoteIdentifier('tournamentId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'gameId')
            ->join(array('b' => 'playersingame'), 'a.' . $gId . ' = b.' . $gId, null)
            ->where('b.' . $pId . ' = ?', $playerId)
            ->where($tId . ' = ?', $tournamentId);

        return $this->selectOne($select);
    }
}

