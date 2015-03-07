<?php

class Cli_Model_NeutralPlayer extends Cli_Model_DefaultPlayer
{
    private $_longName = 'Shadow';
    private $_team = 'neutral';
    private $_backgroundColor = '#808080';

    public function __construct($gameId, $mapCastles, $mapTowers, $playersTowers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_color = 'neutral';

        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();
        $this->_armies = new Cli_Model_Armies();
        $this->initCastles($gameId, $mapCastles, $db);
        $this->initTowers($mapTowers, $playersTowers);
    }

    public function initCastles($gameId, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastleProduction = new Application_Model_CastleProduction($db);
        $playersCastles = $mCastlesInGame->getAllCastles();

        foreach ($mapCastles as $castleId => $castle) {
            if (isset($playersCastles[$castleId])) {
                continue;
            }
            $this->_castles->addCastle($castleId, new Cli_Model_Castle(array(), $castle));
            $this->_castles->getCastle($castleId)->initProduction($mCastleProduction->getCastleProduction($castleId));
        }
    }

    public function initTowers($mapTowers, $playersTowers)
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
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray()
        );
    }

    public function getArmies()
    {
        return new Cli_Model_Armies();
    }

    public function getTeam()
    {
        return $this->_team;
    }

    public function getDefenceSequence()
    {
        return;
    }

    public function getId()
    {
        return 0;
    }
}