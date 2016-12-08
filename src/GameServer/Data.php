<?php
namespace GameServer;
class Data extends \Threaded
{
    private $_dataIn;
    private $_db;

    /**
     * @param $dataIn
     * @throws Exception
     */
    public function __construct($dataIn)
    {
        if (!isset($dataIn->gameId) || !isset($dataIn->playerId) || !isset($dataIn->langId)) {
            throw new Exception('Brak "gameId" lub "playerId" lub "langId');
            return;
        }

        $this->_dataIn = $dataIn;
        $this->_db = Database::getDb();;
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function getData()
    {
        return $this->_dataIn;
    }
}