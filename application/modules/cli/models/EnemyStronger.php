<?php

class Cli_Model_EnemyStronger
{
    private $_stronger = false;

    public function __construct($army, $game, $x, $y, $playerColor, $max = 30)
    {
        $this->_l->logMethodName();

        $attackerWinsCount = 0;
        $attackerCourage = 2;

        $enemies = new Cli_Model_Enemies($game, $x, $y, $playerColor);

        $battle = new Cli_Model_Battle(
            $army,
            $enemies,
            $game
        );

        for ($i = 0; $i < $max; $i++) {
            if ($battle->fight()) {
                $attackerWinsCount++;
            }
        }

        $border = $max - $attackerWinsCount - $attackerCourage;
        if ($attackerWinsCount < $border) {
            $this->_stronger = true;
        }
    }

    public function stronger()
    {
        return $this->_stronger;
    }
}
