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
}