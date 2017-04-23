<?php

class Cli_Model_TutorialOpen extends Cli_Model_CommonOpen
{
    public function handleMe($user, $myColor, $playerId)
    {
        $me = new Cli_Model_TutorialProgressMe($myColor, $playerId);
        $me->initTutorial($this->_db);
        $user->parameters['me'] = $me;
    }
}