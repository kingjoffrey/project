#!/php -q
<?php

// Set timezone of script to UTC inorder to avoid DateTime warnings in
// vendor/zendframework/zend-log/Zend/Log/Logger.php
date_default_timezone_set('UTC');

require_once("../vendor/autoload.php");

defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->getBootstrap()->bootstrap(array('date', 'config', 'modules'));

class Tournament
{
    public function startStage()
    {
        $db = Cli_Model_Database::getDb();
        $mapId = 311;

        $mTournament = new Application_Model_Tournament($db);
        $mTournamentPlayers = new Application_Model_TournamentPlayers($db);
        $mTournamentGames = new Application_Model_TournamentGames($db);
        $mGame = new Application_Model_Game (0, $db);

        $tournamentId = $mTournament->getCurrent();

        $playersId = $mTournamentPlayers->getPlayersId($tournamentId, 1);

        while ($playersId) {
            print_r($playersId);

            $playerId = $this->getPlayerId($playersId);
            print_r($playersId);
            $gameId = $mGame->createGame(array(
                'numberOfPlayers' => 2,
                'mapId' => $mapId,
                'type' => 3
            ), $playerId);

            $mTournamentGames->addGame($tournamentId, $gameId);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $mMapCastles = new Application_Model_MapCastles($mapId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);

            $startPositions = $mMapCastles->getDefaultStartPositions();

            $first = true;

            foreach (array_keys($startPositions) as $sideId) {
                if ($playerId) {
                    $teamId = 1;
                } else {
                    $playerId = $this->getPlayerId($playersId);
                    print_r($playersId);
                    $teamId = 2;
                    if (empty($playerId)) {
                        throw new Exception('kamieni kupa3!');
                    }
                }

                $mPlayersInGame->joinGame($playerId, $sideId, $teamId);

                if ($first) {
                    $mTurn = new Application_Model_TurnHistory($gameId, $db);
                    $mTurn->add($playerId, 1);
                    $mGame->startGame($playerId);
                    $first = false;
                }

                $mHero = new Application_Model_Hero($playerId, $db);
                $mArmy = new Application_Model_Army($gameId, $db);
                $armyId = $mArmy->createArmy($startPositions[$sideId], $playerId);
                $mHeroesInGame->add($armyId, $mHero->getFirstHeroId());
                $mCastlesInGame->addCastle($startPositions[$sideId]['mapCastleId'], $playerId);

                $playerId = 0;
            }
        }
    }

    private function getPlayerId(&$playersId)
    {
        $random = rand(0, count($playersId) - 1);
        $i = 0;

        foreach ($playersId as $key => $val) {
            if ($i == $random) {
                unset($playersId[$key]);

                return $val['playerId'];
            }
            $i++;
        }
    }
}

$t = new Tournament();
$t->startStage();