<?php

class Cli_Model_EditorPlayer extends Cli_Model_DefaultPlayer
{
    private $_miniMapColor;
    private $_textColor;

    public function __construct($player, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $player['sideId'];

        $this->_miniMapColor = $player['minimapColor'];
        $this->_backgroundColor = $player['backgroundColor'];
        $this->_textColor = $player['textColor'];

        $this->_color = $player['shortName'];

        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();
        $this->_armies = new Cli_Model_Armies();

        $this->initCastles($mapCastles, $db);
    }

    private function initCastles($mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastleProduction = new Application_Model_MapCastleProduction($db);
        foreach ($mapCastles as $castleId => $c) {
            if ($c['sideId'] == $this->_id) {
                $castle = new Cli_Model_EditorCastle(null, $c);
                $castle->initProduction($mCastleProduction->getCastleProduction($castleId));
                $this->_castles->addCastle($castleId, $castle);
                if ($castle->getCapital()) {
                    $this->_capitalId = $castleId;
                }
            }
        }
    }

    public function toArray()
    {
        return array(
            'miniMapColor' => $this->_miniMapColor,
            'backgroundColor' => $this->_backgroundColor,
            'textColor' => $this->_textColor,
            'armies' => $this->_armies->toArray(),
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray(),
            'capitalId' => $this->_capitalId
        );
    }
}