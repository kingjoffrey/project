<?php

class Cli_TutorialHandler extends Cli_CommonHandler
{
    public function open($dataIn, $user)
    {
        new Cli_Model_TutorialOpen($dataIn, $user, $this);
    }
}
