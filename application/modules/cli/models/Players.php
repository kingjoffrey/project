<?php

class Cli_Model_Players
{
    private $_players = array();

    public function get()
    {
        return $this->_players;
    }

    public function getKeys()
    {
        return array_keys($this->_players);
    }

    public function addPlayer($color, $player)
    {
        $this->_players[$color] = $player;
    }

    /**
     * @param $color
     * @return Cli_Model_Player|Cli_Model_EditorPlayer
     */
    public function getPlayer($color)
    {
        if (!$color) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no $color');
        }
        return $this->_players[$color];
    }

    public function toArray()
    {
        $players = array();
        foreach ($this->getKeys() as $color) {
            $players[$color] = $this->getPlayer($color)->toArray();
        }
        return $players;
    }

    public function sameTeam($color1, $color2)
    {
        if ($color1 == $color2) {
            return true;
        }
        return $this->getPlayer($color1)->getTeamId() == $this->getPlayer($color2)->getTeamId();
    }

    public function noEnemyCastles($playerColor)
    {
        $playerTeam = $this->getPlayer($playerColor)->getTeamId();
        foreach ($this->getKeys() as $color) {
            $player = $this->getPlayer($color);
            if ($color == $playerColor || $playerTeam == $player->getTeamId()) {
                continue;
            }
            if ($player->getCastles()->castlesExists()) {
                return false;
            }
        }
        return true;
    }


    public function allEnemiesAreDead($playerColor)
    {
        $playerTeam = $this->getPlayer($playerColor)->getTeamId();
        foreach ($this->getKeys() as $color) {
            $player = $this->getPlayer($color);
            if ($color == $playerColor || $playerTeam == $player->getTeamId()) {
                continue;
            }
            if ($player->getCastles()->castlesExists() || $player->getArmies()->exists()) {
                return false;
            }
        }
        return true;
    }

    public function getEnemies($playerColor)
    {
        $playerTeam = $this->getPlayer($playerColor)->getTeamId();
        $enemies = array();
        foreach ($this->getKeys() as $color) {
            if ($color == 'neutral') {
                continue;
            }
            $player = $this->getPlayer($color);
            if ($color == $playerColor || $playerTeam == $player->getTeamId()) {
                continue;
            }
            $enemies = array_merge($enemies, $player->getArmies()->getArray());
        }
        return $enemies;
    }

    public function initFields($fields)
    {
        foreach ($this->getKeys() as $color) {
            $player = $this->getPlayer($color);
            $player->getArmies()->initFields($fields, $color);
            $player->getCastles()->initFields($fields, $color);
            $player->getTowers()->initFields($fields, $color);
        }
    }

    public function activatePlayerTurn($playerColor, $playerId, $gameId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mPlayersInGame->turnActivate($playerId);

        foreach ($this->getKeys() as $color) {
            if ($color == 'neutral') {
                continue;
            }
            if ($playerColor == $color) {
                $this->getPlayer($color)->setTurnActive(true);
            } else {
                $this->getPlayer($color)->setTurnActive(false);
            }
        }
    }
}