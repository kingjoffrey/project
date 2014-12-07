<?php

class Cli_Model_Castles
{
    private $_castles = array();

    public function get()
    {
        return $this->_castles;
    }

    public function getKeys()
    {
        return array_keys($this->_castles);
    }

    public function addCastle($castleId, Cli_Model_Castle $castle, $oldColor = null, $playerId = null, $gameId = null, $db = null)
    {
        $this->_castles[$castleId] = $castle;
        if ($db) {
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            if ($oldColor == 'neutral') {
                $mCastlesInGame->addCastle($castleId, $playerId);
            } else {
                if ($castle->getDefenseModifier() > 1) {
                    $castle->decreaseDefenceMod();
                }
                $mCastlesInGame->changeOwner($castle, $playerId);
            }
        }
    }

    public function removeCastle($castleId)
    {
        unset($this->_castles[$castleId]);
    }

    /**
     * @param $castleId
     * @return Cli_Model_Castle
     */
    public function getCastle($castleId)
    {
        if ($this->hasCastle($castleId)) {
            return $this->_castles[$castleId];
        }
    }

    public function toArray()
    {
        $castles = array();
        foreach ($this->_castles as $castleId => $castle) {
            $castles[$castleId] = $castle->toArray();
        }
        return $castles;
    }

    public function hasCastle($castleId)
    {
        return isset($this->_castles[$castleId]);
    }

    public function initFields($fields, $color)
    {
        foreach ($this->_castles as $castleId => $castle) {
            $fields->initCastle($castle->getX(), $castle->getY(), $castleId, $color);
        }
    }

    public function noCastlesExists()
    {
        return !count($this->_castles);
    }

    public function castlesExists()
    {
        return count($this->_castles);
    }
}