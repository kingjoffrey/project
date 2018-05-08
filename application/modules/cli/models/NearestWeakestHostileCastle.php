<?php

class Cli_Model_NearestWeakestHostileCastle
{
    private $_path = null;

    public function __construct(Cli_Model_Game $game, $playerColor, Cli_Model_Army $army)
    {
        $this->_l = new Coret_Model_Logger('Cli_Model_NearestWeakestHostileCastle');

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

                $nearestWeakestHostileCastles[$castleId] = array();

                $model_StrengthOfMyEnemy = new Cli_Model_StrengthOfMyEnemy($army, $game, $castle->getX(), $castle->getY(), $playerColor, 10);
                $numberOfTurnsNeeded = 1;

                if (!$model_StrengthOfMyEnemy->isStronger()) {
                    $aStar = new Cli_Model_Astar($army, $castle->getX(), $castle->getY(), $game);
                    $mPath = $aStar->path();
                    $nearestWeakestHostileCastles[$castleId]['path'] = $mPath->getFullPath();

                    while ($mPath->getCurrentDestinationX() != $mPath->getFullDestinationX() && $mPath->getCurrentDestinationY() != $mPath->getFullDestinationY()) {
                        $numberOfTurnsNeeded++;

                        $mPath->cutCurrentPathFromFull();
                        $mPath->computeCurrentPath(true);

                        if($numberOfTurnsNeeded > 1000){
                            throw new Exception('trochę przegięcie');
                        }
                    }

                    $nearestWeakestHostileCastles[$castleId]['score'] = $numberOfTurnsNeeded + $model_StrengthOfMyEnemy->getEnemyStrength() / 20;
                }
            }
        }

        $score = 10;

        $this->findTheWeakest($nearestWeakestHostileCastles, $score);
    }

    private function findTheWeakest($nearestWeakestHostileCastles, $score)
    {
        if (count($nearestWeakestHostileCastles)) {
            $this->_path = end($nearestWeakestHostileCastles)['path'];
            return;
        }

        foreach ($nearestWeakestHostileCastles as $castleId => $arr) {
            if ($arr['score'] > $score) {
                unset($nearestWeakestHostileCastles[$castleId]);
            } else {
                $this->findTheWeakest($nearestWeakestHostileCastles, $arr['score']);
            }
        }
    }

    public function getPath()
    {
        return $this->_path;
    }
}