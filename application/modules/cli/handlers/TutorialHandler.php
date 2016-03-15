<?php

class Cli_TutorialHandler extends Cli_CommonHandler
{
    public function open($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        new Cli_Model_TutorialOpen($dataIn, $user, $this);
    }

    public function ruin($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        $giveMeDragons = true;
        new Cli_Model_SearchRuinHandler($armyId, $user, $this, $giveMeDragons);
    }

    public function handleTutorial($token, Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        $me = Cli_TutorialHandler::getMeFromUser($user);
        switch ($me->getNumber()) {
            case 0:
                $step = $me->getStep();
                switch ($step) {
                    case 0:
                        if ($token['type'] == 'production') {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 1:
                        if ($token['type'] == 'nextTurn') {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 2:
                        if ($token['type'] == 'move') {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 3:
                        if ($token['type'] == 'move' && isset($token['battle']['victory']) && $token['battle']['victory']) {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 4:
                        if ($token['type'] == 'production' && $token['relocationToCastleId']) {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 5:
                        if ($token['type'] == 'end') {
                            $me->setStep(0, $this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                            $me->increaseNumber($this->_db);
                        }
                        break;
                }
                break;
            case 1:
                $step = $me->getStep();
                switch ($step) {
                    case 0:
                        if ($token['type'] == 'startTurn') {
                            foreach ($token['armies'] as $army) {
                                if ($army['swim']) {
                                    $me->increaseStep($this->_db);
                                    if ($army['heroes']) {
                                        $me->increaseStep($this->_db);
                                        $step++;
                                    }
                                    $this->sendToUser($user, array(
                                        'type' => 'step',
                                        'step' => $step + 1
                                    ));
                                    break;
                                }
                            }
                        }
                        break;
                    case 1:
                        if ($token['type'] == 'move' && $token['army']['swim'] && $token['army']['heroes']) {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }

                        break;
                    case 2:
                        if ($token['type'] == 'move' && !$token['army']['swim'] && $token['army']['heroes']) {
                            $me->increaseStep($this->_db);
                            $game = Cli_CommonHandler::getGameFromUser($user);
                            $field = $game->getFields()->getField($token['army']['x'], $token['army']['y']);
                            if ($field->getRuinId()) {
                                $me->increaseStep($this->_db);
                                $step++;
                            }
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 3:
                        if ($token['type'] == 'move' && $token['army']['heroes']) {
                            $game = Cli_CommonHandler::getGameFromUser($user);
                            $field = $game->getFields()->getField($token['army']['x'], $token['army']['y']);
                            if ($field->getRuinId()) {
                                $me->increaseStep($this->_db);
                                $this->sendToUser($user, array(
                                    'type' => 'step',
                                    'step' => $step + 1
                                ));
                            }
                        }
                        break;
                    case 4:
                        if ($token['type'] == 'ruin') {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 5:
                        if ($token['type'] == 'end') {
                            $me->setStep(0, $this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                            $me->increaseNumber($this->_db);
                        }
                        break;
                }
                break;
            case 2:
                $step = $me->getStep();
                switch ($step) {
                    case 0:
                        if ($token['type'] == 'defense' && $token['defense'] == 2) {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 1:
                        if ($token['type'] == 'move') {
                            $game = Cli_CommonHandler::getGameFromUser($user);
                            if ($game->getPlayers()->getPlayer($me->getColor())->getTowers()->count() == 8) {
                                $me->increaseStep($this->_db);
                                $this->sendToUser($user, array(
                                    'type' => 'step',
                                    'step' => $step + 1
                                ));
                            }
                        }
                        break;
                    case 2:
                        if ($token['type'] == 'defense' && $token['defense'] == 4) {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 3:
                        if ($token['type'] == 'startTurn') {
                            foreach ($token['armies'] as $army) {
                                if ($army['fly']) {
                                    $me->increaseStep($this->_db);
                                    $this->sendToUser($user, array(
                                        'type' => 'step',
                                        'step' => $step + 1
                                    ));
                                    break;
                                }
                            }
                        }
                        break;
                    case 4:
                        if ($token['type'] == 'end') {
                            $me->setStep(0, $this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                            $me->resetNumber($this->_db);
                        }
                        break;
                }
                break;
        }
    }

    public function sendToChannel(Cli_Model_Game $game, $token, $debug = null)
    {
        foreach ($game->getUsers() as $user) {
        }
        $this->handleTutorial($token, $user);
        parent::sendToChannel($game, $token, $debug);
    }

    public function sendToUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $token, $debug = null)
    {
        $this->handleTutorial($token, $user);
        parent::sendToUser($user, $token, $debug);
    }

    /**
     * @param WebSocketTransportInterface $user
     * @return Cli_Model_TutorialMe
     */
    static public function getMeFromUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['me'];
    }
}
