<?php

class Cli_Model_Move extends Thread
{
    private $_dataIn;
    private $_user;
    private $_handler;

    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $this->_dataIn = $dataIn;
        $this->_user = $user;
        $this->_handler = $handler;
    }

    public function run()
    {
        if (!isset($this->_dataIn['armyId'])) {
            $this->_handler->sendError($this->_user, 'No "armyId"!');
            return;
        }

        if (!isset($this->_dataIn['x'])) {
            $this->_handler->sendError($this->_user, 'No "x"!');
            return;
        }

        if (!isset($this->_dataIn['y'])) {
            $this->_handler->sendError($this->_user, 'No "y"!');
            return;
        }

        $attackerArmyId = $this->_dataIn['armyId'];
        $x = $this->_dataIn['x'];
        $y = $this->_dataIn['y'];

        $playerId = $this->_user->parameters['me']->getId();
        $game = Cli_CommonHandler::getGameFromUser($this->_user);

        if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
            $this->_handler->sendError($this->_user, 'Niepoprawny format danych!');
            return;
        }

        $players = $game->getPlayers();
        $attackerColor = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($attackerColor);
        $army = $player->getArmies()->getArmy($attackerArmyId);

        if (empty($army)) {
            $this->_handler->sendError($this->_user, 'Brak armii o podanym ID! Odświerz przeglądarkę.');
            return;
        }

        $fields = $game->getFields();

        $armyX = $army->getX();
        $armyY = $army->getY();

        switch ($fields->getField($armyX, $armyY)->getType()) {
            case 'w':
                $otherArmyId = $fields->isPlayerArmy($armyX, $armyY, $attackerColor);
                if ($otherArmyId) {
                    $otherArmy = $player->getArmies()->getArmy($otherArmyId);
                    if (!$otherArmy->canSwim() && !$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $this->_user, $this->_handler);
                        $this->_handler->sendError($this->_user, 'Nie możesz zostawić armii na wodzie.');
                        return;
                    }
                }
                break;
            case'M':
                $otherArmyId = $fields->isPlayerArmy($armyX, $armyY, $attackerColor);
                if ($otherArmyId) {
                    $otherArmy = $player->getArmies()->getArmy($otherArmyId);
                    if (!$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $this->_user, $this->_handler);
                        $this->_handler->sendError($this->_user, 'Nie możesz zostawić armii w górach.');
                        return;
                    }
                }
                break;
        }

        try {
            $A_Star = new Cli_Model_Astar($army, $x, $y, $game);
            $path = $A_Star->path();
        } catch (Exception $e) {
            $l = new Coret_Model_Logger();
            $l->log($e);
            $this->_handler->sendError($this->_user, 'Wystąpił błąd podczas obliczania ścieżki');
            return;
        }

        $army->move($game, $path, $this->_handler);
    }
}
