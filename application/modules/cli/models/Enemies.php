<?php

class Cli_Model_Enemies
{
    private $_enemies = array();
    private $_castleId;
    private $_castleColor;

    public function __construct(Cli_Model_Game $game, $x, $y, $playerColor)
    {
        $fields = $game->getFields();
        $players = $game->getPlayers();
        if ($castleId = $fields->getCastleId($x, $y)) {
            $castleColor = $fields->getCastleColor($x, $y);
            if ($castleColor == 'neutral') {
                $this->neutralCastleGarrison($game, $castleId);
            } elseif ($players->sameTeam($castleColor, $playerColor)) {
                return;
            } else {
                $this->handleCastleGarrison($players->getPlayer($castleColor)->getCastles()->getCastle($castleId), $fields, $players);
            }
            $this->_castleId = $castleId;
            $this->_castleColor = $castleColor;
        } elseif ($armies = $fields->getField($x, $y)->getArmies()) {
            foreach ($armies as $armyId => $armyColor) {
                if ($players->sameTeam($armyColor, $playerColor)) {
                    return;
                }
                $this->_enemies[] = $players->getPlayer($armyColor)->getArmies()->getArmy($armyId);
            }
        }
    }

    public function get()
    {
        return $this->_enemies;
    }

    public function hasEnemies()
    {
        return count($this->_enemies) || $this->_castleId;
    }

    private function handleCastleGarrison(Cli_Model_Castle $castle, Cli_Model_Fields $fields, Cli_Model_Players $players)
    {
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        for ($i = $castleX; $i <= $castleX + 1; $i++) {
            for ($j = $castleY; $j <= $castleY + 1; $j++) {
                foreach ($fields->getField($i, $j)->getArmies() as $armyId => $color) {
                    $this->_enemies[] = $players->getPlayer($color)->getArmies()->getArmy($armyId);
                }
            }
        }
    }

    private function neutralCastleGarrison(Cli_Model_Game $game, $castleId)
    {
        $turnNumber = $game->getTurnNumber();
        $firstUnitId = $game->getFirstUnitId();
        $castle = $game->getPlayers()->getPlayer('neutral')->getCastles()->getCastle($castleId);
        $numberOfSoldiers = ceil($turnNumber / 10);
        $units = Zend_Registry::get('units');

        $army = new Cli_Model_Army(array(
            'armyId' => 0,
            'x' => $castle->getX(),
            'y' => $castle->getY()
        ), 'neutral');
        for ($i = 1; $i <= $numberOfSoldiers; $i++) {
            $soldierId = 's' . $i;
            $army->getSoldiers()->add($soldierId, new Cli_Model_Soldier(array(
                'defensePoints' => 3,
                'soldierId' => $soldierId,
                'unitId' => $firstUnitId
            ), $units->getUnit($firstUnitId)));
        }

        $this->_enemies = array($army);
    }

    public function getCastleId()
    {
        return $this->_castleId;
    }

    public function getCastleColor()
    {
        return $this->_castleColor;
    }
}