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
    }
}