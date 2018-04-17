<?php

class Cli_Model_GameRuins extends Cli_Model_Ruins
{
    public function add($ruinId, Cli_Model_GameRuin $ruin)
    {
        $this->_ruins[$ruinId] = $ruin;
    }

    /**
     * @param $ruinId
     * @return Cli_Model_GameRuin
     */
    public function getRuin($ruinId)
    {
        return $this->_ruins[$ruinId];
    }
}
