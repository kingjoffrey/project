<?php

class Cli_Model_NearestWeakestHostileCastle
{
    private $_path = null;

    public function __construct(Cli_Model_Game $game, $playerColor, Cli_Model_Army $army)
    {
//        $l = new Coret_Model_Logger('Cli_Model_NearestWeakestHostileCastle');

        $nearestWeakestHostileCastles = array();

        $players = $game->getPlayers();

        foreach ($players->getKeys() as $color) {
            if ($players->sameTeam($playerColor, $color)) {
                continue;
            }

            $castles = $players->getPlayer($color)->getCastles();
            foreach ($castles->getKeys() as $castleId) {
                $castle = $castles->getCastle($castleId);

                if ($castle->getEnclaveNumber()) {
                    continue;
                }

                $model_StrengthOfMyEnemy = new Cli_Model_StrengthOfMyEnemy($army, $game, $castle->getX(), $castle->getY(), $playerColor, 10);

                if ($model_StrengthOfMyEnemy->isStronger()) {
                    continue;
                }
                $aStar = new Cli_Model_Astar($army, $castle->getX(), $castle->getY(), $game);
                $mPath = $aStar->path();
                if (!$mPath->exists()) {
                    continue;
                }

                $nearestWeakestHostileCastles[$castleId] = array('path' => $mPath);

                $numberOfTurnsNeeded = 1;

                if ($mPath->enemyInRange()) {
                    $nearestWeakestHostileCastles[$castleId]['score'] = $numberOfTurnsNeeded + $model_StrengthOfMyEnemy->getEnemyStrength() / 20;
                    continue;
                }

                while (true) {
                    $numberOfTurnsNeeded++;

                    $mPath->removeCurrentPathFromFull();
                    $mPath->computeCurrentPath(true);

                    if ($mPath->enemyInTmpRange()) {
                        $nearestWeakestHostileCastles[$castleId]['score'] = $numberOfTurnsNeeded + $model_StrengthOfMyEnemy->getEnemyStrength() / 20;
                        break;
                    }

                    if ($mPath->getTmpCurrentDestinationX() == $mPath->getFullDestinationX() && $mPath->getTmpCurrentDestinationY() == $mPath->getFullDestinationY()) {
                        $nearestWeakestHostileCastles[$castleId]['score'] = $numberOfTurnsNeeded + $model_StrengthOfMyEnemy->getEnemyStrength() / 20;
                        break;
                    }

                    if ($numberOfTurnsNeeded > 1000) {
                        print_r($mPath->getFullPath());
                        print_r($mPath->getTmpCurrentDestinationX());
                        print_r($mPath->getTmpCurrentDestinationY());
                        throw new Exception('trochę przegięcie');
                    }
                }
            }
        }

        $score = 10;

        reset($nearestWeakestHostileCastles);
        $this->findTheWeakest($nearestWeakestHostileCastles, $score);
    }

    private function findTheWeakest($nearestWeakestHostileCastles, $score)
    {
        $count = count($nearestWeakestHostileCastles);
        if ($count == 1) {
            $this->_path = end($nearestWeakestHostileCastles)['path'];
            return;
        } elseif ($count < 1) {
            return;
        }

        $castleId = key($nearestWeakestHostileCastles);

        if (!isset($nearestWeakestHostileCastles[$castleId])) {
            print_r($nearestWeakestHostileCastles);
            print_r($castleId);
            throw new Exception('co jest do chuja pana');
            exit;
        }

        next($nearestWeakestHostileCastles);

        if ($nearestWeakestHostileCastles[$castleId]['score'] >= $score) {
            unset($nearestWeakestHostileCastles[$castleId]);
            $this->findTheWeakest($nearestWeakestHostileCastles, $score);
        } else {
            reset($nearestWeakestHostileCastles);
            $this->findTheWeakest($nearestWeakestHostileCastles, $nearestWeakestHostileCastles[$castleId]['score']);
        }

    }

    public function getPath()
    {
        return $this->_path;
    }
}