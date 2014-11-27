<?php

class Cli_Model_Fight
{
    private $_l;
    private $_result;
    private $_game;
    private $_gameId;
    private $_fields;
    private $_players;
    private $_player;
    private $_army;
    private $_enemies;

    public function __construct(Cli_Model_Game $game, Cli_Model_Army $army, $playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();
        $this->_l->logMethodName();

        $this->_result = new Cli_Model_FightResult();

        $this->_game = $game;
        $this->_gameId = $this->_game->getId();
        $this->_fields = $this->_game->getFields();
        $this->_players = $this->_game->getPlayers();
        $this->_attackerColor = $this->_game->getPlayerColor($playerId);
        $this->_player = $this->_players->getPlayer($this->_attackerColor);
        $this->_army = $army;
    }

    public function prepareArmies(Cli_Model_Path $path)
    {
        $this->_enemies = array();

        if ($castleId = $this->_fields->getCastleId($path->x, $path->y)) {
            $defenderColor = $this->_fields->getCastleColor($path->x, $path->y);
            if ($defenderColor == 'neutral') {
                $this->_enemies = $this->_players->getPlayer($defenderColor)->getCastleGarrison($this->_game->getTurnNumber(), $this->_game->getFirstUnitId());
            } else {
                $this->_enemies = $this->_game->handleCastleGarrison($this->_players->getPlayer($this->_fields->getCastleColor($path->x, $path->y))->getCastle($castleId));
            }
        } elseif ($enemyArmies = $this->_fields->getArmies($path->x, $path->y)) {
            foreach ($enemyArmies as $armyId => $color) {
                $this->_enemies[] = $this->_players->getPlayer($color)->getArmy($armyId);
            }
        } else {
            throw new Exception();
        }
    }

    public function battle()
    {
        if (empty($this->_enemies)) {
            throw new Exception();
        }
        $battle = new Cli_Model_Battle($this->_army, $this->_enemies, $this->_game);
        $battle->fight();
        $battle->prepareResult();

    }

    public function getResult()
    {
        if (empty($this->_result)) {
            throw new Exception();
        }
        return $this->_result;
    }
}

