<?php

class Cli_Model_EditorRuins extends Cli_Model_Ruins
{
    public function editorAdd($ruinId, Cli_Model_EditorRuin $ruin)
    {
        $this->_ruins[$ruinId] = $ruin;
    }

    /**
     * @param $ruinId
     * @return Cli_Model_EditorRuin
     */
    public function getRuin($ruinId)
    {
        self::getRuin($ruinId);
    }

    public function remove($ruinId)
    {
        unset($this->_ruins[$ruinId]);
    }
}
