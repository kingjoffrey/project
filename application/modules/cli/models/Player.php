<?php

class Cli_Model_Player extends Cli_Model_DefaultPlayer
{
    private $_turnActive;
    private $_computer;
    private $_lost;

    private $_gold;

    private $_miniMapColor;
    private $_textColor;

    private $_attackSequence;
    private $_defenceSequence;

    private $_capitalId;

    public function __construct($player, $gameId, $mapCastles, $mapTowers, $playersTowers, Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $player['playerId'];
        $this->_lost = $player['lost'];

        $this->_turnActive = $player['turnActive'];
        $this->_computer = $player['computer'];
        $this->_gold = $player['gold'];
        $this->_miniMapColor = $player['minimapColor'];
        $this->_backgroundColor = $player['backgroundColor'];
        $this->_textColor = $player['textColor'];
        $this->_longName = $player['longName'];

        $this->_team = $mMapPlayers->getColorByMapPlayerId($player['team']);
        $this->_color = $player['color'];

        $this->_armies = new Cli_Model_Armies();
        $this->_castles = new Cli_Model_Castles();
        $this->_towers = new Cli_Model_Towers();

        if ($this->_lost) {
            return;
        }

        $this->initArmies($gameId, $db);
        $this->initCastles($gameId, $mapCastles, $db);
        $this->initTowers($mapTowers, $playersTowers);
        $this->initBattleSequence($gameId, $db);
    }

    private function initBattleSequence($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mBattleSequence = new Application_Model_BattleSequence($gameId, $db);
        $battleSequence = $mBattleSequence->get($this->_id);
        if (empty($battleSequence['attack'])) {
            $mBattleSequence->initiate($this->_id, Zend_Registry::get('units'));
            $battleSequence = $mBattleSequence->get($this->_id);
        }
        $this->setAttackBattleSequence($battleSequence['attack']);
        $this->setDefenceBattleSequence($battleSequence['defence']);
    }

    public function setAttackBattleSequence($sequence)
    {
        $this->_attackSequence = $sequence;
    }

    public function setDefenceBattleSequence($sequence)
    {
        $this->_defenceSequence = $sequence;
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

    private function initCastles($gameId, $mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mCastleProduction = new Application_Model_CastleProduction($db);
        foreach ($mCastlesInGame->getPlayerCastles($this->_id) as $castleId => $c) {
            $this->_castles->addCastle($castleId, new Cli_Model_Castle($c, $mapCastles[$castleId]));
            $castle = $this->_castles->getCastle($castleId);
            $castle->initProduction($mCastleProduction->getCastleProduction($castleId));
            if ($castle->isCapital()) {
                $this->_capitalId = $castleId;
            }
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
            'turnActive' => $this->_turnActive,
            'computer' => $this->_computer,
            'lost' => $this->_lost,
            'miniMapColor' => $this->_miniMapColor,
            'backgroundColor' => $this->_backgroundColor,
            'textColor' => $this->_textColor,
            'longName' => $this->_longName,
            'team' => $this->_team,
            'armies' => $this->_armies->toArray(),
            'castles' => $this->_castles->toArray(),
            'towers' => $this->_towers->toArray()
        );
    }

    public function setLost($gameId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mPlayersInGame->setPlayerLostGame($this->_id);
        $this->_lost = true;
    }

    public function getGold()
    {
        return $this->_gold;
    }

    public function subtractGold($gold)
    {
        $this->_gold -= $gold;
    }

    public function addGold($gold)
    {
        $this->_gold += $gold;
    }

    public function setTurnActive($turnActive)
    {
        $this->_turnActive = $turnActive;
    }

    public function addTower($towerId, Cli_Model_Tower $tower, $oldColor, Cli_Model_Fields $fields, $gameId, $db)
    {
        $fields->getField($tower->getX(), $tower->getY())->setTowerColor($this->_color);
        $this->_towers->add($towerId, $tower, $oldColor, $this->_id, $gameId, $db);
    }

    public function getComputer()
    {
        return $this->_computer;
    }

    public function getTurnActive()
    {
        return $this->_turnActive;
    }

    public function getAttackSequence()
    {
        return $this->_attackSequence;
    }

    public function getDefenceSequence()
    {
        return $this->_defenceSequence;
    }

    public function noArmiesAndCastles()
    {
        return $this->_castles->noCastlesExists() && $this->_armies->noArmiesExists();
    }

    public function armiesOrCastlesExists()
    {
        return $this->_armies->exists() || $this->_castles->castlesExists();
    }

    public function saveGold($gameId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mPlayersInGame->updatePlayerGold($this->_id, $this->_gold);
    }

    public function getCapitalId()
    {
        return $this->_capitalId;
    }
}