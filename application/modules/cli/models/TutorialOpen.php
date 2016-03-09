<?php

class Cli_Model_TutorialOpen extends Cli_Model_CommonOpen
{
    public function me($user, $myColor, $playerId)
    {
        $me = new Cli_Model_TutorialMe($myColor, $playerId);
        $me->initTutorial($this->_db);
        $user->parameters['me'] = $me;
    }
}