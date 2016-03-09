<?php

class Cli_TutorialHandler extends Cli_CommonHandler
{
    protected $_baseClassName = 'Cli_Model_Tutorial';

    public function open($dataIn, $user)
    {
        new Cli_Model_TutorialOpen($dataIn, $user, $this);
    }
}
