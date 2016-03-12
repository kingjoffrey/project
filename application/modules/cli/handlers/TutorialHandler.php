<?php

class Cli_TutorialHandler extends Cli_CommonHandler
{
    public function open($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        new Cli_Model_TutorialOpen($dataIn, $user, $this);
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
                            $me->setStep($step + 1, $me->getNumber(), $me->getId(), $this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 1:
                        if ($token['type'] == 'nextTurn') {
                            $me->setStep($step + 1, $me->getNumber(), $me->getId(), $this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 2:
                        if ($token['type'] == 'move') {
                            $me->setStep($step + 1, $me->getNumber(), $me->getId(), $this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 3:
                        if ($token['type'] == 'move' && isset($token['battle']['victory']) && $token['battle']['victory']) {
                            $me->setStep($step + 1, $me->getNumber(), $me->getId(), $this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 4:

                        break;
                    case 5:

                        break;
                }
                break;
            case 1:
                switch ($me->getStep()) {
                    case 0:
                        break;
                    case 1:
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                    case 4:
                        break;
                    case 5:
                        break;
                }
                break;
            case 2:
                switch ($me->getStep()) {
                    case 0:
                        break;
                    case 1:
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                    case 4:
                        break;
                    case 5:
                        break;
                }
                break;
        }
    }

    public function sendToChannel(Cli_Model_Game $game, $token, $debug = null)
    {
        foreach ($game->getUsers() as $user) {
            echo 'a';
        }
//        if ($user) {
            $this->handleTutorial($token, $user);
//        }
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
