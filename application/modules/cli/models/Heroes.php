<?php

class Cli_Model_Heroes
{
    private $_heroes = array();

    public function get()
    {
        return $this->_heroes;
    }

    /**
     * @param $heroId
     * @return Cli_Model_Hero
     */
    public function getHero($heroId)
    {
        return $this->_heroes[$heroId];
    }

    public function toArray()
    {
        $heroes = array();
        foreach ($this->_heroes as $heroId => $hero) {
            $heroes[$heroId] = $hero->toArray();
        }
        return $heroes;
    }

    public function exists()
    {
        return count($this->_heroes);
    }

    public function getAnyHeroId()
    {
        reset($this->_heroes);
        return key($this->_heroes);
    }

    public function hasHero()
    {
        return count($this->_heroes);
    }

    public function remove($heroId)
    {
        unset($this->_heroes[$heroId]);
    }

    public function add($heroId, $hero)
    {
        $this->_heroes[$heroId] = $hero;
    }

    public function getKeys()
    {
        return array_keys($this->_heroes);
    }

    public function saveMove($x, $y, $movesLeft, $type, Cli_Model_Path $path, $gameId, $db)
    {
        if (!count($this->_heroes)) {
            return $movesLeft;
        }

        $terrain = Zend_Registry::get('terrain');
        $current = $path->getCurrent();
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($this->getKeys() as $heroId) {
            $hero = $this->getHero($heroId);
            $movesSpend = 0;

            foreach ($current as $step) {
                if ($step['x'] == $x && $step['y'] == $y) {
                    break;
                }
                if (!isset($step['cc'])) {
                    $movesSpend += $terrain[$step['tt']][$type];
                }
            }

            $hero->updateMovesLeft($movesSpend, $mHeroesInGame);

            if ($movesLeft > $hero->getMovesLeft()) {
                $movesLeft = $hero->getMovesLeft();
            }
        }

        return $movesLeft;
    }
}