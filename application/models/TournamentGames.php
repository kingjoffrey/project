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
        $gameIden = $this->_db->quoteIdentifier('gameId');
        $playerIden = $this->_db->quoteIdentifier('playerId');
        $tournamentIden = $this->_db->quoteIdentifier('tournamentId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'gameId')
            ->join(array('b' => 'playersingame'), 'a.' . $gameIden . ' = b.' . $gameIden, null)
            ->join(array('c' => 'game'), 'a.' . $gameIden . ' = c.' . $gameIden, null)
            ->where('b.' . $playerIden . ' = ?', $playerId)
            ->where($this->_db->quoteIdentifier('isActive') . ' = true')
            ->where($tournamentIden . ' = ?', $tournamentId);

        return $this->selectOne($select);
    }

    public function getTournamentId($gameId)
    {
        $gameIden = $this->_db->quoteIdentifier('gameId');

        $select = $this->_db->select()
            ->from($this->_name, 'tournamentId')
            ->where($gameIden . ' = ?', $gameId);

        return $this->selectOne($select);
    }
}

