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
                'type' => 0,
                'mapId' => $mapId
            ), $user->parameters['playerId']);

            $mPlayer = new Application_Model_Player($db);
            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $mMapCastles = new Application_Model_MapCastles($mapId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            $first = true;
            $startPositions = $mMapCastles->getDefaultStartPositions();

            $playerId = $user->parameters['playerId'];
            foreach (array_keys($startPositions) as $sideId) {
                if ($playerId) {
                    $teamId = 1;
                } else {
                    $playerId = $mPlayer->getComputerPlayerId($mPlayersInGame->getOtherComputerPlayerIdSelect());
                    $teamId = 2;
                    if (empty($playerId)) {
                        throw new Exception('kamieni kupa2!');
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

        $token = array(
            'type' => 'tutorial',
            'action' => 'index',
            'steps' => $tutorial->toArray(),
            'gameId' => $gameId
        );

        $handler->sendToUser($user, $token);
    }
}