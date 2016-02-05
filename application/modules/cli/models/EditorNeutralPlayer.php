<?php

class Cli_Model_EditorNeutralPlayer extends Cli_Model_DefaultPlayer
{
    public function __construct($mapCastles, $mapTowers)
    {
        $this->_id = 0;

        $this->_color = 'neutral';
        $this->_team = 'neutral';

        $this->_longName = 'Shadow';
        $this->_backgroundColor = '#808080';

        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();
        $this->_armies = new Cli_Model_Armies();

        $this->initCastles($mapCastles);
        $this->initTowers($mapTowers);
    }

    private function initCastles($mapCastles)
    {
        $numberOfSoldiers = 1;
        foreach ($mapCastles as $castleId => $c) {
            if ($c['mapPlayerId'] == $this->_id) {
                $castle = new Cli_Model_EditorCastle();
                $castle->init($c);
                $this->_castles->addCastle($castleId, $castle);
            }

//            $armyId = 'a' . $castle->getId();
//            $army = new Cli_Model_Army(array(
//                'armyId' => $armyId,
//                'x' => $castle->getX(),
//                'y' => $castle->getY()
//            ), $this->_color);
//            for ($i = 0; $i < $numberOfSoldiers; $i++) {
//                $soldierId = 's' . $i;
//                $army->getWalkingSoldiers()->add($soldierId, new Cli_Model_Soldier(array(
//                    'soldierId' => $soldierId,
//                    'unitId' => $firstUnitId
//                ), $units->getUnit($firstUnitId)));
//            }
//
//            $this->_armies->addArmy($armyId, $army);
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
            'team' => $this->_team,
            'backgroundColor' => $this->_backgroundColor,
            'armies' => $this->_armies->toArray(),
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray()
        );
    }
}