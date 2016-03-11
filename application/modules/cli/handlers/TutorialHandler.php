<?php

class Cli_TutorialHandler extends Cli_CommonHandler
{
    public function open($dataIn, $user)
    {
        new Cli_Model_TutorialOpen($dataIn, $user, $this);
    }

    public function childMessageHandler($dataIn, $user)
    {
        $me = Cli_TutorialHandler::getMeFromUser($user);
        switch ($me->getTutorialNumber()) {
            case 0:
                switch ($me->getStep()) {
                    case 0:
                        if ($dataIn['type'] == 'production') {
                            $me->setStep(1, $me->getTutorialNumber(), $me->getId(), $this->_db);
                        }
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

    /**
     * @param WebSocketTransportInterface $user
     * @return Cli_Model_TutorialMe
     */
    static public function getMeFromUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['me'];
    }
}
