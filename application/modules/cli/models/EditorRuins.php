<?php

class Cli_Model_EditorRuins
{
    private $_ruins = array();

    public function toArray()
    {
        $ruins = array();
        foreach ($this->_ruins as $ruinId => $ruin) {
            $ruins[$ruinId] = $ruin->toArray();
        }
        return $ruins;
    }

    public function get()
    {
        return $this->_ruins;
    }

    public function getKeys()
    {
        return array_keys($this->_ruins);
    }

    public function createRuin($x, $y, $mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $ruin = new Cli_Model_EditorRuin($x, $y);
        $ruin->create($mapId, $db);
        $this->_ruins[$ruin->getId()] = $ruin;
    }

    public function add($id, Cli_Model_EditorRuin $ruin)
    {
        $ruin->add($id);
        $this->_ruins[$id] = $ruin;
    }

    /**
     * @param $ruinId
     * @return Cli_Model_Ruin
     */
    public function getRuin($ruinId)
    {
        return $this->_ruins[$ruinId];
    }
}
