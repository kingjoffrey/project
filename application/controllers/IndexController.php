<?php

class IndexController extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';
    protected $_playerId;
    protected $_version;

    public function indexAction()
    {
        $identity = $this->_auth->getIdentity();
        $this->_playerId = $identity->playerId;
        $this->_version = Zend_Registry::get('config')->version;

        $this->view->headLink()->prependStylesheet('/css/main.css?v=' . $this->_version);
        $this->view->headLink()->appendStylesheet('/css/help.css?v=' . $this->_version);
        $this->view->headLink()->appendStylesheet('/css/editor.css?v=' . $this->_version);
        $this->view->headLink()->appendStylesheet('/css/new.css?v=' . $this->_version);
        $this->view->headLink()->appendStylesheet('/css/playerslist.css?v=' . $this->_version);

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/jquery.mousewheel.min.js');
        $this->view->headScript()->appendFile('/js/Tween.js');
        $this->view->headScript()->appendFile('/js/three/three.js');
        $this->view->headScript()->appendFile('/js/three/Detector.js');

        $this->view->headScript()->appendFile('/js/default.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/libs.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/main/init.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/main.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/editor.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/load.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/halloffame.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/help.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/play.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/players.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/profile.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/websocket.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/websocketMessage.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/main/websocketSend.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/help/init.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/help.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/helpModels.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/helpScene.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/helpRenderer.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/websocket.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/websocketMessage.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/websocketSend.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/halloffame.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/editor/init.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/message.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/castle.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/castleWindow.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/editor.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/models.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/websocket.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/gui.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/picker.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/websocketSend.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/editor/websocketMessage.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/game/player.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/game/unit.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/game/minimap.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/common/init.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/castle.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/castles.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/gameRenderer.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/gameScene.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/ground.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/gameModels.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/field.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/fields.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/picker.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/players.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/ruin.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/ruins.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/tower.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/towers.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/units.js?v=' . $this->_version);

        $this->view->models();
//        $this->view->MainMenu();
        $this->view->Friends();
        $this->view->ChatInput();
        $this->view->ChatTitle();
        $this->view->FriendsTitle();
        $this->view->translations();
//        $this->view->googleAnalytics();
        $this->view->Version();

        $this->view->Websocket($identity);
    }

    protected function authorized()
    {
        $this->view->Logout();
    }

    public function unsupportedAction()
    {

    }
}
