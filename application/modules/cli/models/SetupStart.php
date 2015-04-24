<?php

class Cli_Model_SetupStart
{
    /**
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_SetupHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_SetupHandler $handler)
    {
        $setup = Cli_Model_Setup::getSetup($user);
        if (!$setup->isGameMaster($user->parameters['playerId'])) {
            echo('Not game master!');
            return;
        }

        $setup->setIsOpen(false);
        $db = $handler->getDb();

        $mPlayersInGame = new Application_Model_PlayersInGame($setup->getId(), $db);
        $mPlayersInGame->disconnectNotActive();

        $players = $mPlayersInGame->getAll();

        $mGame = new Application_Model_Game($setup->getId(), $db);
        $mapId = $mGame->getMapId();

        $mMapCastles = new Application_Model_MapCastles($mapId, $db);
        $startPositions = $mMapCastles->getDefaultStartPositions();

        $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
        $mapPlayers = $mMapPlayers->getAll();

        $first = true;

        foreach ($mapPlayers as $mapPlayerId => $mapPlayer) {
            if (isset($players[$mapPlayerId])) {
                $playerId = $players[$mapPlayerId]['playerId'];
            } else {
                $playerId = $mPlayersInGame->getComputerPlayerId();
                if (!$playerId) {
                    $modelPlayer = new Application_Model_Player($db);
                    $playerId = $modelPlayer->createComputerPlayer();
                    $modelHero = new Application_Model_Hero($playerId, $db);
                    $modelHero->createHero();
                }
            }

            $mPlayersInGame->joinGame($playerId, $mapPlayerId);

            if ($first) {
                $mTurn = new Application_Model_TurnHistory($setup->getId(), $db);
                $mTurn->add($playerId, 1);
                $mGame->startGame($playerId);
                $first = false;
            }

            $mPlayersInGame->setTeam($playerId, $dataIn['team'][$mapPlayerId]);

            $mHero = new Application_Model_Hero($playerId, $db);
            $playerHeroes = $mHero->getHeroes();
            if (empty($playerHeroes)) {
                $mHero->createHero();
                $playerHeroes = $mHero->getHeroes($playerId, $db);
            }
            $mArmy = new Application_Model_Army($setup->getId(), $db);

            $armyId = $mArmy->createArmy($startPositions[$mapPlayer['castleId']], $playerId);

            $mHeroesInGame = new Application_Model_HeroesInGame($setup->getId(), $db);
            $mHeroesInGame->add($armyId, $playerHeroes[0]['heroId']);

            $mCastlesInGame = new Application_Model_CastlesInGame($setup->getId(), $db);
            $mCastlesInGame->addCastle($mapPlayer['castleId'], $playerId);
        }

        $token = array('type' => 'start');
        $handler->sendToChannel($setup, $token);
    }
}