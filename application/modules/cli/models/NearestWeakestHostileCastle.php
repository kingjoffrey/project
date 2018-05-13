<?php

class Cli_Model_NearestWeakestHostileCastle
{
    private $_path = null;

    public function __construct(Cli_Model_Game $game, $playerColor, Cli_Model_Army $army)
    {
//        $this->_l = new Coret_Model_Logger('Cli_Model_NearestWeakestHostileCastle');

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
//                    $this->_l->log('Stronger (enemy strength=' . $model_StrengthOfMyEnemy->getEnemyStrength() . ') (castleId=' . $castleId . ')');
                    continue;
                }

                $aStar = new Cli_Model_Astar($army, $castle->getX(), $castle->getY(), $game);
                $mPath = $aStar->path();
                if (!$mPath->exists()) {
//                    $this->_l->log('No path (castleId=' . $castleId . ')');
                    continue;
                }

                $nearestWeakestHostileCastles[$castleId] = array('path' => $mPath);

                $numberOfTurnsNeeded = 1;

                if ($mPath->enemyInRange()) {
                    $nearestWeakestHostileCastles[$castleId]['score'] = $numberOfTurnsNeeded + $model_StrengthOfMyEnemy->getEnemyStrength() / 20;
//                    $this->_l->log('In range (castleId=' . $castleId . ') (score=' . $nearestWeakestHostileCastles[$castleId]['score'] . ')');
                    continue;
                }

                while (true) {
                    $numberOfTurnsNeeded++;

                    $mPath->removeCurrentPathFromFull();
                    $mPath->computeCurrentPath(true);

                    if ($mPath->enemyInTmpRange()) {
                        $nearestWeakestHostileCastles[$castleId]['score'] = $numberOfTurnsNeeded + $model_StrengthOfMyEnemy->getEnemyStrength() / 20;
//                        $this->_l->log('In TMP range (castleId=' . $castleId . ') (score=' . $nearestWeakestHostileCastles[$castleId]['score'] . ')');
                        break;
                    }

                    if ($mPath->getTmpCurrentDestinationX() == $mPath->getFullDestinationX() && $mPath->getTmpCurrentDestinationY() == $mPath->getFullDestinationY()) {
                        $nearestWeakestHostileCastles[$castleId]['score'] = $numberOfTurnsNeeded + $model_StrengthOfMyEnemy->getEnemyStrength() / 20;
//                        $this->_l->log('End of path (castleId=' . $castleId . ') (score=' . $nearestWeakestHostileCastles[$castleId]['score'] . ')');
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

        reset($nearestWeakestHostileCastles);
        $this->findTheWeakest($nearestWeakestHostileCastles);
    }

    private function findTheWeakest($nearestWeakestHostileCastles)
    {
        $score = 10;

        foreach ($nearestWeakestHostileCastles as $castleId => $castle) {
            if ($castle['score'] < $score) {
//                $this->_l->log('castleId=' . $castleId . ' score=' . $castle['score']);

                $score = $castle['score'];
                $this->_path = $castle['path'];
            }
        }
    }

    public function getPath()
    {
        return $this->_path;
    }
}