<?php

class Cli_Model_Open
{

    private $_parameters = array();

    public function __construct($dataIn, $user, $db, $gameHandler)
    {
        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            $gameHandler->sendError($user, 'Brak "gameId" lub "playerId" lub "langId');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($dataIn['gameId'], $db);

        if (!$mPlayersInGame->checkAccessKey($dataIn['playerId'], $dataIn['accessKey'], $db)) {
            $gameHandler->sendError($user, 'Brak uprawnień!');
            return;
        }

        $mPlayersInGame->updateWSSUId($dataIn['playerId'], $user->getId());

        $mGame = new Application_Model_Game($dataIn['gameId'], $db);
        $mBattleSequence = new Application_Model_BattleSequence($dataIn['gameId'], $db);

        $game = $mGame->getGame();

        $mMapFields = new Application_Model_MapFields($game['mapId'], $db);
        $mMapCastles = new Application_Model_MapCastles($game['mapId'], $db);
        $mMapRuins = new Application_Model_MapRuins($game['mapId'], $db);
        $mMapTowers = new Application_Model_MapTowers($game['mapId'], $db);
        $mMapUnits = new Application_Model_MapUnits($game['mapId'], $db);
        $mMapPlayers = new Application_Model_MapPlayers($game['mapId'], $db);
        $mMapTerrain = new Application_Model_MapTerrain($game['mapId'], $db);

        Zend_Registry::set('battleSequence', $mBattleSequence->get($dataIn['playerId']));
        Zend_Registry::set('id_lang', $dataIn['langId']);
        Zend_Registry::set('terrain', $mMapTerrain->getTerrain());
        $units = $mMapUnits->getUnits();
        Zend_Registry::set('units', $units);
        $specialUnits = array();
        foreach ($units as $unit) {
            if ($unit['special']) {
                $specialUnits[] = $unit;
            }
        }
        Zend_Registry::set('specialUnits', $specialUnits);
        reset($units);
        Zend_Registry::set('firstUnitId', key($units));
        Zend_Registry::set('fields', $mMapFields->getMapFields());
        $castles = $mMapCastles->getMapCastles();
        $mCastleProduction = new Application_Model_CastleProduction($db);
        foreach (array_keys($castles) as $castleId) {
            $castles[$castleId]['production'] = $mCastleProduction->getCastleProduction($castleId);
        }
        Zend_Registry::set('castles', $castles);
        Zend_Registry::set('ruins', $mMapRuins->getMapRuins());
        Zend_Registry::set('towers', $mMapTowers->getMapTowers());
        $playersInGameColors = $mPlayersInGame->getAllColors();
        Zend_Registry::set('playersInGameColors', $playersInGameColors);
        Zend_Registry::set('teams', $mPlayersInGame->getTeams());
        Zend_Registry::set('capitals', $mMapPlayers->getCapitals());

        $mTurn = new Application_Model_TurnHistory($dataIn['gameId'], $db);
        $turn = $mTurn->getCurrentStatus();

        $this->_parameters = array(
            'gameId' => $dataIn['gameId'],
            'playerId' => $dataIn['playerId'],
            'langId' => $dataIn['langId'],
            'begin' => strtotime($game['begin']),
            'turnsLimit' => $game['turnsLimit'],
            'turnTimeLimit' => $game['turnTimeLimit'],
            'timeLimit' => $game['timeLimit'],
            'turnNumber' => $turn['number'],
            'turnStart' => strtotime($turn['date']),
        );

        $online = array();

        foreach ($mPlayersInGame->getInGamePlayerIds() as $row) {
            $online[$playersInGameColors[$row['playerId']]] = 1;
        }

        $token = array(
            'type' => 'open',
            'color' => $playersInGameColors[$dataIn['playerId']],
            'online' => $online
        );

        $gameHandler->sendToChannel($db, $token, $dataIn['gameId']);
    }

    public function getParameters()
    {
        return $this->_parameters;
    }

}