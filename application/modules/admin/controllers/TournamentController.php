<?php

class Admin_TournamentController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Turniej';
        parent::init();
    }

    public function stageAction()
    {
        $mTournament = new Application_Model_Tournament();
        $mTournamentPlayers = new Application_Model_TournamentPlayers();
        $mTournamentGames = new Application_Model_TournamentGames();
        $mGame = new Application_Model_Game (0);

        $tournament = $mTournament->getCurrent();

        if (empty($tournament)) {
            echo 'Brak turnieju (zakończony)' . "\n";
            return;
        }

        $playersId = $mTournamentPlayers->getPlayersId($tournament['tournamentId']);
        print_r($playersId);

        $playersLeft = count($playersId);

        if ($playersLeft <= 2) {
            $mTournament->end($tournament['tournamentId']);
        }

        if ($playersLeft <= 1) {
            echo 'Mamy zwycięzcę' . "\n";
            return;
        }

        while ($playersId) {
            $playerId = $this->getPlayerId($playersId);

            $gameId = $mGame->createGame(array(
                'numberOfPlayers' => 2,
                'mapId' => $tournament['mapId'],
                'type' => Zend_Registry::get('config')->game->type->tournament
            ), $playerId);

            $mTournamentGames->addGame($tournament['tournamentId'], $gameId);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
            $mMapCastles = new Application_Model_MapCastles($tournament['mapId']);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId);
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId);

            $startPositions = $mMapCastles->getDefaultStartPositions();

            $first = true;

            foreach (array_keys($startPositions) as $sideId) {
                if ($playerId) {
                    $teamId = 1;
                } else {
                    $playerId = $this->getPlayerId($playersId);

                    $teamId = 2;
                    if (empty($playerId)) {
                        throw new Exception('kamieni kupa3!');
                    }
                }

                $mPlayersInGame->joinGame($playerId, $sideId, $teamId);

                if ($first) {
                    $mTurn = new Application_Model_TurnHistory($gameId);
                    $mTurn->add($playerId, 1);
                    $mGame->startGame($playerId);
                    $first = false;
                }

                $mHero = new Application_Model_Hero($playerId);
                $mArmy = new Application_Model_Army($gameId);
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

