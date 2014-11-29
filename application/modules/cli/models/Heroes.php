<?php

class Cli_Model_Heroes
{
    private $_heroes = array();

    public function get()
    {
        return $this->_heroes;
    }

    public function addHero($heroId, $hero)
    {
        $this->_heroes[$heroId] = $hero;
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

    public function setHeroes($heroes)
    {
        foreach ($heroes as $hero) {
            $this->_heroes->addHero($hero['heroId'], new Cli_Model_Hero($hero));
        }
    }

    public function exists()
    {
        return count($this->_heroes);
    }

    public function add($heroes)
    {
        $this->_heroes = array_merge($this->_heroes, $heroes);
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
}