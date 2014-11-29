<?php

/**
 * Class Cli_Model_Hero
 * ver. 0001
 */
class Cli_Model_Hero extends Cli_Model_DefaultUnit
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
    }

    public function toArray()
    {
        return array(
            'moves' => $this->_moves,
            'attack' => $this->_attack,
            'defense' => $this->_defense,
            'name' => $this->_name,
            'movesLeft' => $this->_movesLeft
        );
    }

    public function updateMovesLeft($heroId, $movesSpend, Application_Model_HeroesInGame $mHeroesInGame)
    {
        $this->_movesLeft -= $movesSpend;
        if ($this->_movesLeft < 0) {
            echo $this->_movesLeft . "\n";
            echo $movesSpend . "\n";
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('movesLeft < 0');
        }

        $mHeroesInGame->updateMovesLeft($this->_movesLeft, $heroId);
    }

    public function resetMovesLeft($gameId, $db)
    {
        if ($this->_movesLeft > 2) {
            $this->setMovesLeft($this->_moves + 2);
        } else {
            $this->setMovesLeft($this->_moves);
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->updateMovesLeft($this->_movesLeft, $this->_id);
    }

    public function zeroMovesLeft($gameId, $db)
    {
        $this->_movesLeft = 0;
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->zeroHeroMovesLeft($this->_id);
    }

    public function death($playerId, $gameId, $db)
    {
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->armyRemoveHero($this->_id);
        $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
        $mHeroesKilled->add($this->_id, 0, $playerId);
    }
}