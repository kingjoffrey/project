<?php

abstract class Cli_Model_ComputerMethods
{
    /**
     * @var Cli_Model_Common
     */
    protected $_game;
    protected $_gameId;
    protected $_playerId;

    /**
     * @var Cli_Model_Players
     */
    protected $_players;

    /**
     * @var Cli_Model_Player
     */
    protected $_player;

    /**
     * @var Cli_Model_Fields
     */
    protected $_fields;

    /**
     * @var Zend_Db_Adapter_Pdo_Pgsql
     */
    protected $_db;

    /**
     * @var Cli_Model_Army
     */
    protected $_army;

    /**
     * @var Cli_CommonHandler
     */
    protected $_handler;

    /**
     * @var Coret_Model_Logger
     */
    protected $_l;

    public function __construct(Cli_Model_Army $army, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $this->_army = $army;
        $this->_user = $user;
        $this->_game = Cli_CommonHandler::getGameFromUser($user);
        $this->_db = $handler->getDb();
        $this->_handler = $handler;
        $this->_playerId = $this->_game->getTurnPlayerId();
        $this->_players = $this->_game->getPlayers();
        $this->_color = $this->_game->getPlayerColor($this->_playerId);
        $this->_player = $this->_players->getPlayer($this->_color);
        $this->_armyId = $this->_army->getId();
        $this->_armyX = $this->_army->getX();
        $this->_armyY = $this->_army->getY();
        $this->_movesLeft = $this->_army->getMovesLeft();
        $this->_gameId = $this->_game->getId();
        $this->_fields = $this->_game->getFields();
    }

    protected function getPathToMyArmyInRange()
    {
        $this->_l->logMethodName();
        $myArmies = new Cli_Model_Armies();

        foreach ($this->_player->getArmies()->getKeys() as $armyId) {
            if ($armyId == $this->_armyId) {
                continue;
            }
            $army = $this->_player->getArmies()->getArmy($armyId);
            if ($army->getX() == $this->_armyX && $army->getY() == $this->_armyY) {
                continue;
            }
            $myArmies->addArmy($armyId, $army);
        }

        foreach ($myArmies->getKeys() as $armyId) {
            $army = $myArmies->getArmy($armyId);

            $mHeuristics = new Cli_Model_Heuristics($army->getX(), $army->getY());
            if ($mHeuristics->calculateH($this->_armyX, $this->_armyY) > $this->_movesLeft) {
                continue;
            }

            try {
                $aStar = new Cli_Model_Astar($this->_army, $army->getX(), $army->getY(), $this->_game);
                return $aStar->path();
            } catch (Exception $e) {
                echo($e);
                return;
            }
        }
    }

    protected function getPathToMyClosestArmy()
    {
        $this->_l->logMethodName();
        $myArmies = new Cli_Model_Armies();
        $armyHeuristics = array();


        foreach ($this->_player->getArmies()->getKeys() as $armyId) {
            if ($armyId == $this->_armyId) {
                continue;
            }
            $myArmies->addArmy($armyId, $this->_player->getArmies()->getArmy($armyId));
        }

        foreach ($myArmies->getKeys() as $armyId) {
            $army = $myArmies->getArmy($armyId);
            if ($army->getX() == $this->_armyX && $army->getY() == $this->_armyY) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($army->getX(), $army->getY());
            $armyHeuristics[$armyId] = $mHeuristics->calculateWithFieldsCosts($this->_armyX, $this->_armyY, $this->_fields, $this->_game->getTerrain());
        }

        asort($armyHeuristics, SORT_NUMERIC);
        reset($armyHeuristics);
        if ($armyId = key($armyHeuristics)) {
            $army = $myArmies->getArmy($armyId);
        } else {
            return;
        }

        try {
            $aStar = new Cli_Model_Astar($this->_army, $army->getX(), $army->getY(), $this->_game);
            return $aStar->path();
        } catch (Exception $e) {
            echo($e);
            return;
        }
    }

    protected function getPathToMyClosestCastle()
    {
        $this->_l->logMethodName();
        $castlesHeuristics = array();
        $castles = $this->_player->getCastles();

        foreach ($castles->getKeys() as $castleId) {
            $castle = $castles->getCastle($castleId);
            if ($this->_armyX == $castle->getX() && $this->_armyY == $castle->getY()) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($castle->getX(), $castle->getY());
            $castlesHeuristics[$castleId] = $mHeuristics->calculateWithFieldsCosts($this->_armyX, $this->_armyY, $this->_fields, $this->_game->getTerrain());
        }

        asort($castlesHeuristics, SORT_NUMERIC);
        reset($castlesHeuristics);
        if ($castleId = key($castlesHeuristics)) {
            $castle = $castles->getCastle($castleId);
        } else {
            return;
        }

        try {
            $aStar = new Cli_Model_Astar($this->_army, $castle->getX(), $castle->getY(), $this->_game);
            return $aStar->path();
        } catch (Exception $e) {
            echo($e);
            return;
        }
    }

    protected function getStrongerEnemyArmyInRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            $armies = $this->_players->getPlayer($color)->getArmies();
            foreach ($armies->getKeys() as $enemyId) {
                $enemy = $armies->getArmy($enemyId);
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();

                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                if ($mHeuristics->calculateH($this->_armyX, $this->_armyY) > $this->_movesLeft) {
                    continue;
                }

                $model_StrengthOfMyEnemy = new Cli_Model_StrengthOfMyEnemy($this->_army, $this->_game, $enemyX, $enemyY, $this->_color);
                if (!$model_StrengthOfMyEnemy->isStronger()) {
                    continue;
                }

                try {
                    $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_game);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                return $aStar->path();
            }
        }
        return null;
    }

    /**
     * @return Cli_Model_Path
     */
    protected function getWeakerEnemyArmyInRange()
    {
        $this->_l->logMethodName();

        foreach ($this->_players->getKeys() as $color) {
            if ($this->_players->sameTeam($this->_color, $color)) {
                continue;
            }
            $armies = $this->_players->getPlayer($color)->getArmies();
            foreach ($armies->getKeys() as $enemyId) {
                $enemy = $armies->getArmy($enemyId);
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();

                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                if ($mHeuristics->calculateH($this->_armyX, $this->_armyY) > $this->_movesLeft) {
                    continue;
                }

                $model_StrengthOfMyEnemy = new Cli_Model_StrengthOfMyEnemy($this->_army, $this->_game, $enemyX, $enemyY, $this->_color);
                if ($model_StrengthOfMyEnemy->isStronger()) {
                    continue;
                }

                try {
                    $aStar = new Cli_Model_Astar($this->_army, $enemyX, $enemyY, $this->_game);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                return $aStar->path();

            }
        }
    }

    /**
     * @param $enemy
     * @return Cli_Model_Path
     */
    protected function isEnemyArmyInRange(Cli_Model_Army $enemy)
    {
        $this->_l->logMethodName();

        try {
            $aStar = new Cli_Model_Astar($this->_army, $enemy->getX(), $enemy->getY(), $this->_game);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $aStar->path();
    }
}

