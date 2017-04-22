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
}

