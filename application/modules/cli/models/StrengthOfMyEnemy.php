<?php

class Cli_Model_StrengthOfMyEnemy
{
    private $_loopMaximum = 100;
    private $_myStrength;
    private $_myCourage;

    public function __construct(Cli_Model_Army $army,
                                Cli_Model_Game $game,
                                $enemyX, $enemyY,
                                $playerColor,
                                $myCourage = 0)
    {
        $this->_myCourage = $myCourage;
        $battle = new Cli_Model_Battle($army,
            new Cli_Model_Enemies($game, $enemyX, $enemyY, $playerColor),
            $game
        );

        $numberOfMyWins = 0;

        for ($i = 0; $i <= $this->_loopMaximum; $i++) {
            if ($battle->fight()) {
                $numberOfMyWins++;
            }
        }

        $this->_myStrength = $numberOfMyWins;
    }

    public function getMyStrength()
    {
        return $this->_myStrength;
    }

    public function getEnemyStrength()
    {
        return $this->_loopMaximum - $this->_myStrength;
    }

    public function isStronger()
    {
        if ($this->_myStrength + $this->_myCourage < $this->_loopMaximum / 2) {
            return true;
        }
    }
}
