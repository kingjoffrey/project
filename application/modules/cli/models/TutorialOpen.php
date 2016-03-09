<?php

class Cli_Model_TutorialOpen extends Cli_Model_CommonOpen
{
    public function me($user, $myColor, $playerId)
    {
        $user->parameters['me'] = new Cli_Model_TutorialMe($myColor, $playerId);
    }
}