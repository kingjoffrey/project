<?php

class Cli_Model_EditorNeutralPlayer extends Cli_Model_DefaultPlayer
{
    public function __construct($mapCastles, $mapTowers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = 0;

        $this->_color = 'neutral';
        $this->_teamId = 'neutral';

        $this->_longName = 'Shadow';
        $this->_backgroundColor = '#808080';

        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();
        $this->_armies = new Cli_Model_Armies();

        $this->initCastles($mapCastles, $db);
        $this->initTowers($mapTowers);
    }

    private function initCastles($mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastleProduction = new Application_Model_MapCastleProduction($db);
        foreach ($mapCastles as $castleId => $c) {
            if ($c['sideId'] == $this->_id) {
                $castle = new Cli_Model_EditorCastle(null, $c);
                $castle->initProduction($mCastleProduction->getCastleProduction($castleId));
                $this->_castles->addCastle($castleId, $castle);
            }
        }
    }

    private function initTowers($mapTowers)
    {
        foreach ($mapTowers as $towerId => $tower) {
            $tower['towerId'] = $towerId;
            $this->_towers->add($towerId, new Cli_Model_Tower($tower));
        }
    }

    public function toArray()
    {
        return array(
            'longName' => $this->_longName,
            'team' => $this->_teamId,
            'backgroundColor' => $this->_backgroundColor,
            'armies' => $this->_armies->toArray(),
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray()
        );
    }
}