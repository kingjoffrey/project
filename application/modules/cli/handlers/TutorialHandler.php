<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_TutorialHandler extends Cli_CommonHandler
{
    public function open($dataIn, WebSocketTransportInterface $user)
    {
        new Cli_Model_TutorialOpen($dataIn, $user, $this);
    }

    public function ruin($armyId, WebSocketTransportInterface $user)
    {
        $giveMeDragons = true;
        new Cli_Model_SearchRuinHandler($armyId, $user, $this, $giveMeDragons);
    }

    public function handleTutorial($token, WebSocketTransportInterface $user)
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
                        if ($token['type'] == 'end') {
                            $me->setStep(0, $this->_db);
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
                            $field = $this->_game->getFields()->getField($token['army']['x'], $token['army']['y']);
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
                            $field = $this->_game->getFields()->getField($token['army']['x'], $token['army']['y']);
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
                            $me->increaseNumber($this->_db);
                        }
                        break;
                }
                break;
            case 2:
                $step = $me->getStep();
                switch ($step) {
                    case 0:
                        if ($token['type'] == 'move') {
                            if ($this->_game->getPlayers()->getPlayer($me->getColor())->getTowers()->count() == 8) {
                                $me->increaseStep($this->_db);
                                $this->sendToUser($user, array(
                                    'type' => 'step',
                                    'step' => $step + 1
                                ));
                            }
                        }
                        break;
                    case 1:
                        if ($token['type'] == 'production') {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 2:
                        if ($token['type'] == 'defense' && $token['defense'] == 2) {
                            $me->increaseStep($this->_db);
                            $this->sendToUser($user, array(
                                'type' => 'step',
                                'step' => $step + 1
                            ));
                        }
                        break;
                    case 3:
                        if ($token['type'] == 'end') {
                            $me->setStep(0, $this->_db);
                            $me->resetNumber($this->_db);
                        }
                        break;
                }
                break;
        }
    }

    public function sendToChannel($token, $debug = null)
    {
        foreach ($this->_game->getUsers() as $user) {
        }
        $this->handleTutorial($token, $user);
        parent::sendToChannel($token, $debug);
    }

    public function sendToUser(WebSocketTransportInterface $user, $token, $debug = null)
    {
        $this->handleTutorial($token, $user);
        parent::sendToUser($user, $token, $debug);
    }

    /**
     * @param WebSocketTransportInterface $user
     * @return Cli_Model_TutorialProgressMe
     */
    static public function getMeFromUser(WebSocketTransportInterface $user)
    {
        return $user->parameters['me'];
    }
}
