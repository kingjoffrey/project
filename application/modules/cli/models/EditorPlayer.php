<?php

class Cli_Model_EditorPlayer extends Cli_Model_DefaultPlayer
{
    private $_miniMapColor;
    private $_textColor;

    public function __construct($player, $mapCastles, $mapTowers, $playersTowers, Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $player['mapPlayerId'];

        $this->_miniMapColor = $player['minimapColor'];
        $this->_backgroundColor = $player['backgroundColor'];
        $this->_textColor = $player['textColor'];
        $this->_longName = $player['longName'];

        $this->_color = $player['shortName'];

        $this->_armies = new Cli_Model_Armies();
        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();

//        $this->initArmies($gameId, $db);
        $this->initCastles($mapCastles, $db);
//        $this->initTowers($mapTowers, $playersTowers);
    }

    private function initArmies($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($mArmy->getPlayerArmies($this->_id) as $a) {
            $this->_armies->addArmy($a['armyId'], new Cli_Model_Army($a, $this->_color));
            $army = $this->_armies->getArmy($a['armyId']);
            $army->initHeroes($mHeroesInGame->getForMove($a['armyId']));
            $army->initSoldiers($mSoldier->getForMove($a['armyId']));
        }
    }

    private function initCastles($mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastleProduction = new Application_Model_CastleProduction($db);
        foreach ($mapCastles as $castleId => $c) {
            $this->_castles->addCastle($castleId, new Cli_Model_Castle($c, $mapCastles[$castleId]));
            $castle = $this->_castles->getCastle($castleId);
            $castle->initProduction($mCastleProduction->getCastleProduction($castleId));
        }
    }

    private function initTowers($mapTowers, $playersTowers)
    {
        foreach ($playersTowers as $towerId => $playerId) {
            if ($playerId != $this->_id) {
                continue;
            }
            $tower = $mapTowers[$towerId];
            $tower['towerId'] = $towerId;
            $this->_towers->add($towerId, new Cli_Model_Tower($tower));
        }
    }

    public function toArray()
    {
        return array(
            'miniMapColor' => $this->_miniMapColor,
            'backgroundColor' => $this->_backgroundColor,
            'textColor' => $this->_textColor,
            'longName' => $this->_longName,
            'armies' => $this->_armies->toArray(),
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray()
        );
    }

    public function addTower($towerId, Cli_Model_Tower $tower, $oldColor, Cli_Model_Fields $fields, $gameId, $db)
    {
        $fields->getField($tower->getX(), $tower->getY())->setTowerColor($this->_color);
        $this->_towers->add($towerId, $tower, $oldColor, $this->_id, $gameId, $db);
    }
}