<?php

class TowerController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function addAction(){
        $towerId = $this->_request->getParam('tid');
        $color = $this->_request->getParam('c');
        if ($towerId !== null || empty($color)) {
            $modelTower = new Application_Model_Tower($this->_namespace->gameId);
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $modelGame->getPlayerIdByColor($color);
            if($modelTower->towerExists($towerId)){
                $modelTower->changeTowerOwner($towerId, $playerId);
            }else{
                $modelTower->addTower($towerId, $playerId);
            }
        } else {
            throw new Exception('Brak "towerId"!');
        }
    }

    public function getAction(){
        $towerId = $this->_request->getParam('tid');
        if ($towerId !== null) {
            $modelTower = new Application_Model_Tower($this->_namespace->gameId);
            if($modelTower->towerExists($towerId)){
                $this->view->response = Zend_Json::encode($modelTower->getTower($towerId));
            }else{
                throw new Exception('Nie istnieje!');
            }
        } else {
            throw new Exception('Brak "towerId"!');
        }
    }
}
