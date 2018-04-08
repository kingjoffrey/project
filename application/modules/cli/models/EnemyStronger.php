<?php

class Cli_Model_EnemyStronger
{
    private $_stronger = false;

    public function __construct(Cli_Model_Army $army, Cli_Model_Game $game, $x, $y, $playerColor, $max = 30)
    {
        $attackerWinsCount = 0;
        $attackerCourage = 0;

        $battle = new Cli_Model_Battle(
            $army,
            new Cli_Model_Enemies($game, $x, $y, $playerColor),
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
