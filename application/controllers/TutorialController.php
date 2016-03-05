<?php

class TutorialController extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';
    private $_gameId;

    public function indexAction()
    {
        $this->_gameId = $this->_request->getParam('id');
        if (empty($this->_gameId)) {
            throw new Exception('Brak "gameId"!');
        }

        $version = Zend_Registry::get('config')->version;

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/kinetic-v4.7.4.min.js');
        $this->view->headScript()->appendFile('/js/date.js');

        $this->_helper->layout->setLayout('game');

        $this->view->sound();
        $this->view->models();
        $this->view->translations();
        $this->view->gameId = $this->_gameId;

        $this->view->Websocket($this->_auth->getIdentity());
        $this->view->Friends();

        $mGame = new Application_Model_Game($this->_gameId);
        $this->view->map($mGame->getMapId());


        $this->view->headScript()->appendFile('/js/jquery-ui-1.10.3.custom.js');
        $this->view->headScript()->appendFile('/js/jquery.mousewheel.min.js');
        $this->view->headScript()->appendFile('/js/Tween.js');
        $this->view->headScript()->appendFile('/js/three/three.js');
        $this->view->headScript()->appendFile('/js/three/Detector.js');
        $this->view->headScript()->appendFile('/js/three/Mirror.js');
        $this->view->headScript()->appendFile('/js/three/WaterShader.js');
        $this->view->headScript()->appendFile('/js/geometries/TextGeometry.js');
        $this->view->headScript()->appendFile('/js/utils/FontUtils.js');
        $this->view->headScript()->appendFile('/fonts/helvetiker_regular.typeface.js');

        $this->view->headScript()->appendFile('/js/common/castle.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/castles.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/scene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ground.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/field.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/fields.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/players.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruin.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruins.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/tower.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/towers.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/units.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/game.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/me.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/unit.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/terrain.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/castle.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/players.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/player.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/army.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/message.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/astar.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/chat.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/gui.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/move.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/zoom.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/timer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/turn.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/sound.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/castleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/splitWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/statusWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/battleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/treasuryWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/statisticsWindow.js?v=' . $version);

        $this->view->headLink()->appendStylesheet('/css/game.css?v=' . $version);
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . $version);
    }
}
