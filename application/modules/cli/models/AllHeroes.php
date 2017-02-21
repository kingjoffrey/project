<?php

class Cli_Model_AllHeroes
{
    private $_heroes = array();
    private $_playerId;

    public function __construct($playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mHero = new Application_Model_Hero($playerId, $db);
        $this->_heroes = $mHero->getHeroes();

        $this->_playerId = $playerId;
    }

    public function add(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mHero = new Application_Model_Hero($this->_playerId, $db);
        $mNG = new Cli_Model_NameGenerator();
        $heroId = $mHero->createHero($mNG->generateHeroName());

        $this->_heroes[] = $mHero->getHero($heroId);

        return $heroId;
    }

    public function hire(Cli_Model_Armies $armies, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->_heroes as $hero) {
            $find = 0;

            foreach ($armies->getKeys() as $armyId) {
                if ($armies->getArmy($armyId)->getHeroes()->hasHero($hero['heroId'])) {
                    $find = 1;
                    break;
                }
            }

            if (empty($find)) {
                return $hero['heroId'];
            }
        }

        return $this->add($db);
    }
}