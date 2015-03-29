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
        $this->view->headScript()->appendFile('/fonts/helvetiker_regular.typeface.js');

        $this->view->headScript()->appendFile('/js/game/picker.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/3d.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/game.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/me.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/units.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/unit.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/terrain.js?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile('/js/game/players.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/player.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/castles.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/castle.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/army.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/towers.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/tower.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/ruins.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/ruin.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/fields.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/field.js?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile('/js/game/astar.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/gui.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/move.js?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/js/game/test.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/chat.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/chest.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/libs.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/zoom.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/websocket.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/message.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/timer.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/turn.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/sound.js?v=' . Zend_Registry::get('config')->version);

//        $this->view->headScript()->appendFile('/models/flag.json?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/models/flag_1.json?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/models/ruin.json?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/models/tower.json?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/models/castle.json?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/models/mountain.json?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/models/hill.json?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/models/tree.json?v=' . Zend_Registry::get('config')->version);

        $this->view->sound();
        $this->view->models();

        $this->view->gameId = $this->_gameId;
        $this->view->playerId = $this->_auth->getIdentity()->playerId;

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId);
        $this->view->accessKey = $mPlayersInGame->getAccessKey($this->view->playerId);

        $mGame = new Application_Model_Game($this->_gameId);
        $this->view->map($mapId = $mGame->getMapId());
    }
}

