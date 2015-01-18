<?php

class GameController extends Game_Controller_Game
{

    public function indexAction()
    {
        $this->_helper->layout->setLayout('game');

        $this->view->headLink()->appendStylesheet('/css/game.css?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile('/js/jquery-ui-1.10.3.custom.js');
        $this->view->headScript()->appendFile('/js/jquery.mousewheel.min.js');
//        $this->view->headScript()->appendFile('/js/three.js');
        $this->view->headScript()->appendFile('http://threejs.org/build/three.min.js');
        $this->view->headScript()->appendFile('/js/EventsControls.js');

        $this->view->headScript()->appendFile('/js/game/3d.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/init.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/castles.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/astar.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/gui.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/move.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/towers.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/ruins.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/test.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/chat.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/chest.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/libs.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/zoom.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/websocket.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/message.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/timer.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/players.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/turn.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/sound.js?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile('/models/flag.json?v=' . Zend_Registry::get('config')->version);

        $this->view->sound();

        $this->view->gameId = $this->_gameId;
        $this->view->playerId = $this->_auth->getIdentity()->playerId;

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId);
        $this->view->accessKey = $mPlayersInGame->getAccessKey($this->view->playerId);

        $mGame = new Application_Model_Game($this->_gameId);
        $this->view->map($mapId = $mGame->getMapId());
    }
}

