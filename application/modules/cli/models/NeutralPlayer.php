<?php

class Cli_Model_NeutralPlayer extends Cli_Model_DefaultPlayer
{
    public function __construct(Cli_Model_Game $game, $mapCastles, $mapTowers, $playersTowers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = 0;

        $this->_color = 'neutral';
        $this->_team = 'neutral';

        $this->_longName = 'Shadow';
        $this->_backgroundColor = '#808080';

        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();
        $this->_armies = new Cli_Model_Armies();

        $this->initCastles($game, $mapCastles, $db);
        $this->initTowers($mapTowers, $playersTowers);
    }

    private function initCastles(Cli_Model_Game $game, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $firstUnitId = $game->getFirstUnitId();
        $numberOfSoldiers = $game->getNumberOfGarrisonUnits();
        $units = $game->getUnits();

        $mCastlesInGame = new Application_Model_CastlesInGame($game->getId(), $db);
        $mCastleProduction = new Application_Model_CastleProduction($db);
        $playersCastles = $mCastlesInGame->getAllCastles();

        foreach ($mapCastles as $castleId => $castle) {
            if (isset($playersCastles[$castleId])) {
                continue;
            }
            $this->_castles->addCastle($castleId, new Cli_Model_Castle(array(), $castle));
            $castle = $this->_castles->getCastle($castleId);
            $castle->initProduction($mCastleProduction->getCastleProduction($castleId));

            $armyId = 'a' . $castle->getId();
            $army = new Cli_Model_Army(array(
                'armyId' => $armyId,
                'x' => $castle->getX(),
                'y' => $castle->getY()
            ), $this->_color);
            for ($i = 1; $i <= $numberOfSoldiers; $i++) {
                $soldierId = 's' . $i;
                $army->getWalkingSoldiers()->add($soldierId, new Cli_Model_Soldier(array(
                    'soldierId' => $soldierId,
                    'unitId' => $firstUnitId
                ), $units->getUnit($firstUnitId)));
            }

            $this->_armies->addArmy($armyId, $army);
        }
    }

    public function increaseCastlesGarrison($numberOfGarrisonUnits, $firstUnitId, $units)
    {
        foreach ($this->_castles->getKeys() as $castleId) {
            $castle = $this->_castles->getCastle($castleId);
            $armyId = 'a' . $castle->getId();
            $soldierId = 's' . $numberOfGarrisonUnits;

            $this->_armies->getArmy($armyId)->getWalkingSoldiers()->add($soldierId, new Cli_Model_Soldier(array(
                'soldierId' => $soldierId,
                'unitId' => $firstUnitId
            ), $units->getUnit($firstUnitId)));
        }
    }

    private function initTowers($mapTowers, $playersTowers)
    {
        foreach ($mapTowers as $towerId => $tower) {
            if (isset($playersTowers[$towerId])) {
                continue;
            }
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

    public function getDefenceSequence()
    {
        return;
    }
}