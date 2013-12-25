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

        $mGame = new Application_Model_Game($dataIn['gameId'], $db);

        $game = $mGame->getGame();

        $mMapFields = new Application_Model_MapFields($game['mapId'], $db);
        $mMapCastles = new Application_Model_MapCastles($game['mapId'], $db);
        $mMapRuins = new Application_Model_MapRuins($game['mapId'], $db);
        $mMapTowers = new Application_Model_MapTowers($game['mapId'], $db);
        $mMapUnits = new Application_Model_MapUnits($game['mapId'], $db);
        $mMapPlayers = new Application_Model_MapPlayers($game['mapId'], $db);
        $mMapTerrain = new Application_Model_MapTerrain($game['mapId'], $db);

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
        Zend_Registry::set('playersInGameColors', $mPlayersInGame->getAllColors());
        Zend_Registry::set('capitals', $mMapPlayers->getCapitals());

        $mPlayersInGame->updatePlayerInGameWSSUId($dataIn['playerId'], $user->getId());

        $this->_parameters = array(
            'gameId' => $dataIn['gameId'],
            'playerId' => $dataIn['playerId'],
            'langId' => $dataIn['langId'],
            'turnsLimit' => $game['turnsLimit'],
            'turnTimeLimit' => $game['turnTimeLimit'],
            'timeLimit' => $game['timeLimit'],
        );

        $token = array(
            'type' => 'open'
        );

        $gameHandler->send($user, Zend_Json::encode($token));
    }

    public function getParameters()
    {
        return $this->_parameters;
    }

}