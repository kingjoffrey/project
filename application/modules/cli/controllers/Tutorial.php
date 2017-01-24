<?php

use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class TutorialController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $db = $handler->getDb();
        $playerId = $user->parameters['playerId'];
        $mGame = new Application_Model_Game(0, $db);
        $gameId = $mGame->getMyTutorial($playerId);
        if (!$gameId) {
            $mTutorial = new Application_Model_TutorialProgress($playerId, $db);
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
                'gameMasterId' => $playerId,
                'mapId' => $mapId,
                'turnsLimit' => 0,
                'turnTimeLimit' => 0,
                'timeLimit' => 0,
            ), $playerId);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
            $mMapCastles = new Application_Model_MapCastles($mapId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            $first = true;
            $startPositions = $mMapCastles->getDefaultStartPositions();
            foreach ($mMapPlayers->getAll() as $mapPlayerId => $mapPlayer) {
                if (!$playerId) {
                    $playerId = $mPlayersInGame->getComputerPlayerId();
                    if (!$playerId) {
                        $modelPlayer = new Application_Model_Player($db);
                        $playerId = $modelPlayer->createComputerPlayer();
                        $modelHero = new Application_Model_Hero($playerId, $db);
                        $modelHero->createHero();
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
                $playerHeroes = $mHero->getHeroes();
                if (empty($playerHeroes)) {
                    $mHero->createHero();
                    $playerHeroes = $mHero->getHeroes($playerId);
                }
                $mArmy = new Application_Model_Army($gameId, $db);
                $armyId = $mArmy->createArmy($startPositions[$mapPlayer['mapPlayerId']], $playerId);
                $mHeroesInGame->add($armyId, $playerHeroes[0]['heroId']);
                $mCastlesInGame->addCastle($startPositions[$mapPlayer['mapPlayerId']]['mapCastleId'], $playerId);
                $playerId = 0;
            }
        }

        $layout = new Zend_Layout();
        $layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts');
        $layout->setLayout('game');

        $token = array(
            'type' => 'tutorial',
            'action' => 'index',
            'data' => $layout->render(),
            'gameId' => $gameId
        );

        $handler->sendToUser($user, $token);
    }
}