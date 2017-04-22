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
    public function a()
    {
        $db = Cli_Model_Database::getDb();

        $mTournament = new Application_Model_Tournament($db);
        $mTournamentPlayers = new Application_Model_TournamentPlayers($db);
        $mTournamentGames = new Application_Model_TournamentGames($db);
        $mGame = new Application_Model_Game (0, $db);

        $tournament = $mTournament->getCurrent();
        $players = $mTournamentPlayers->getPlayers($tournament['tournamentId'], $tournament['stage']);

    }

    private function create()
    {
        $gameId = $mGame->createGame(array(
            'numberOfPlayers' => 2,
            'mapId' => 311,
            'type' => 3
        ), $playerId);

        $mTournamentGames->addGame($tournament['tournamentId'],$gameId);

    }
}