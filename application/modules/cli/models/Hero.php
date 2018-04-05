<?php

/**
 * Class Cli_Model_Hero
 * ver. 0001
 */
class Cli_Model_Hero extends Cli_Model_Being
{
    protected $_type = 'hero';
    private $_name;
    private $_mapRuinsBonus;

    public function __construct($hero, $bonus, $mapRuinsBonus)
    {
        $this->_id = $hero['heroId'];
        $this->_moves = $hero['numberOfMoves'];
        $this->_attack = $hero['attackPoints'];
        $this->_defense = $hero['defensePoints'];
        $this->_lifePoints = $hero['lifePoints'];
        $this->_regenerationSpeed = $hero['regenerationSpeed'];
        $this->_name = $hero['name'];
        $this->_movesLeft = $hero['movesLeft'];
        $this->_remainingLife = $hero['remainingLife'];

        $this->_mapRuinsBonus = $mapRuinsBonus;

        foreach ($mapRuinsBonus as $mapRuinId => $type) {
            $this->handleMapRuinBonus($type);
        }

        foreach ($bonus as $key => $row) {
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

    private function handleMapRuinBonus($type)
    {
        switch ($type) {
            case 1:
                $this->_attack++;
                break;
            case 2:
                $this->_defense++;
                break;
        }
    }

    public function hasMapRuinBonus($mapRuinId)
    {
        return $this->_mapRuinsBonus[$mapRuinId];
    }

    public function addMapRuinBonus($mapRuin, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_mapRuinsBonus[$mapRuin['mapRuinId']] = $mapRuin['ruinId'];
        $this->handleMapRuinBonus($mapRuin['ruinId']);

        $mHeroesToMapRuins = new Application_Model_HeroesToMapRuins($db);
        $mHeroesToMapRuins->add($this->_id, $mapRuin['mapRuinId'], $gameId);
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

    public function updateRemainingLife($remainingLife, $gameId = null, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->setRemainingLife($remainingLife);

        if ($gameId) {
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $mHeroesInGame->updateRemainingLife($remainingLife, $this->_id);
        }
    }

    public function regenerateLife(Application_Model_HeroesInGame $mHeroesInGame)
    {
        if ($this->_remainingLife < $this->_lifePoints) {
            $this->_remainingLife += $this->_regenerationSpeed;
            if ($this->_remainingLife > $this->_lifePoints) {
                $this->setRemainingLife($this->_lifePoints);
            }
            $mHeroesInGame->updateRemainingLife($this->_remainingLife, $this->_id);
        }
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