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
}