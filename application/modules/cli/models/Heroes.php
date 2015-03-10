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
        if (!isset($this->_heroes[$heroId])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no $heroId');
        }
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
        return !empty($this->_heroes);
    }

    public function getAnyHeroId()
    {
        reset($this->_heroes);
        return key($this->_heroes);
    }

    public function hasHero($heroId)
    {
        return isset($this->_heroes[$heroId]);
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
        if (empty($this->_heroes)) {
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
                if (!$step['c']) {
                    $movesSpend += $terrain->getTereinType($step['t'])->getCost($type);
                }
            }

            $hero->updateMovesLeft($movesSpend, $mHeroesInGame);

            if ($movesLeft > $hero->getMovesLeft()) {
                $movesLeft = $hero->getMovesLeft();
            }
        }
        return $movesLeft;
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->getKeys() as $heroId) {
            $this->getHero($heroId)->resetMovesLeft($gameId, $db);
        }

    }

    public function count()
    {
        return count($this->_heroes);
    }

    public function getMovesLeft()
    {
        foreach ($this->getKeys() as $heroId) {
            $hero = $this->getHero($heroId);
            if (!isset($movesLeft)) {
                $movesLeft = $hero->getMovesLeft();
                continue;
            }
            if ($hero->getMovesLeft() < $movesLeft) {
                $movesLeft = $hero->getMovesLeft();
            }
        }
        if ($movesLeft) {
            return $movesLeft;
        }
    }
}