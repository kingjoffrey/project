<?php

use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class TutorialController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $db = $handler->getDb();

        if (!$tutorial = $handler->getTutorial()) {
            echo 'not set' . "\n";
            $tutorial = new Cli_Model_Tutorial($db);
            $handler->addTutorial($tutorial);
        }

        $mGame = new Application_Model_Game(0, $db);
        $gameId = $mGame->getMyTutorial($user->parameters['playerId']);
        if (!$gameId) {
            $mTutorial = new Application_Model_TutorialProgress($user->parameters['playerId'], $db);
            $number = $mTutorial->getNumber();
            switch ($number) {
                case 0:
                    $mapId = 296;
                    break;
                case 1:
                    $mapId = 295;
                    break;
                case 2:
                    $mapId = 298;
                    break;
                default:
                    $mapId = 296;
            }
            $gameId = $mGame->createGame(array(
                'numberOfPlayers' => 2,
                'mapId' => $mapId
            ), $user->parameters['playerId']);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
            $mMapCastles = new Application_Model_MapCastles($mapId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            $first = true;
            $startPositions = $mMapCastles->getDefaultStartPositions();

            $playerId = $user->parameters['playerId'];
            foreach ($mMapPlayers->getAll() as $mapPlayerId => $mapPlayer) {
                if (!$playerId) {
                    $playerId = $mPlayersInGame->getComputerPlayerId();
                    if (empty($playerId)) {
                        throw new Exception('kamieni kupa2!');
                    }
                }

                $mPlayersInGame->joinGame($playerId, $mapPlayerId);
                $mPlayersInGame->setTeam($playerId, $mapPlayerId);

                if ($first) {
                    $mTurn = new Application_Model_TurnHistory($gameId, $db);
                    $mTurn->add($playerId, 1);
                    $mGame->startGame($playerId);
                    $first = false;
                }

                $mHero = new Application_Model_Hero($playerId, $db);
                $mArmy = new Application_Model_Army($gameId, $db);
                $armyId = $mArmy->createArmy($startPositions[$mapPlayer['mapPlayerId']], $playerId);
                $mHeroesInGame->add($armyId, $mHero->getFirstHero());
                $mCastlesInGame->addCastle($startPositions[$mapPlayer['mapPlayerId']]['mapCastleId'], $playerId);

                $playerId = 0;
            }
        }

        $token = array(
            'type' => 'tutorial',
            'action' => 'index',
            'steps' => $tutorial->toArray(),
            'gameId' => $gameId
        );

        $handler->sendToUser($user, $token);
    }
}