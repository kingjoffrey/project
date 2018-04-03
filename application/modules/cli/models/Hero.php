<?php

/**
 * Class Cli_Model_Hero
 * ver. 0001
 */
class Cli_Model_Hero extends Cli_Model_Being
{
    protected $_type = 'hero';
    private $_name;

    public function __construct($hero)
    {
        $this->_id = $hero['heroId'];
        $this->_moves = $hero['numberOfMoves'];
        $this->_attack = $hero['attackPoints'];
        $this->_defense = $hero['defensePoints'];
        $this->_name = $hero['name'];
        $this->_movesLeft = $hero['movesLeft'];
        $this->_remainingLife = $hero['remainingLife'];

        foreach ($hero['bonus'] as $key => $row) {
            switch ($row['bId']) {
                case 1:
                    $this->_attack++;
                    break;
                case 2:
                    $this->_defense++;
                    break;
                case 3:
                    $this->_moves++;
                    break;
            }
        }
    }

    public function toArray()
    {
        return array(
            'moves' => $this->_moves,
            'attack' => $this->_attack,
            'defense' => $this->_defense,
            'name' => $this->_name,
            'movesLeft' => $this->_movesLeft,
            'remainingLife' => $this->_remainingLife
        );
    }

    public function updateRemainingLife($remainingLife, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->setRemainingLife($remainingLife);

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->updateRemainingLife($remainingLife, $this->_id);
    }

    public function updateMovesLeft($movesSpend, Application_Model_HeroesInGame $mHeroesInGame)
    {
        $this->_movesLeft -= $movesSpend;
        if ($this->_movesLeft < 0) {
            echo $this->_movesLeft . "\n";
            echo $movesSpend . "\n";
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('movesLeft < 0');
        }

        $mHeroesInGame->updateMovesLeft($this->_movesLeft, $this->_id);
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_movesLeft > 2) {
            $this->setMovesLeft($this->_moves + 2);
        } else {
            $this->setMovesLeft($this->_moves);
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->updateMovesLeft($this->_movesLeft, $this->_id);
    }

    public function zeroMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_movesLeft = 0;
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->updateMovesLeft($this->_movesLeft, $this->_id);
    }

    public function death($gameId, Zend_Db_Adapter_Pdo_Pgsql $db, $winnerId, $loserId)
    {
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->armyRemoveHero($this->_id);
        $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
        $mHeroesKilled->add($this->_id, $winnerId, $loserId);
    }

    public function getStepCost(Cli_Model_TerrainTypes $terrain, $terrainType, $movementType)
    {
        if ($movementType == 'swim') {
            return 0;
        } else {
            return $terrain->getTerrainType($terrainType)->getCost($movementType);
        }
    }
}