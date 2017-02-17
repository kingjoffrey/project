<?php

class IndexController extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';
    protected $_playerId;
    protected $_version;

    public function indexAction()
    {
        $identity = $this->_auth->getIdentity();
        $version = Zend_Registry::get('config')->version;

        $this->view->headLink()->prependStylesheet('/css/main.css?v=' . $version);

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/jquery.mousewheel.min.js');
        $this->view->headScript()->appendFile('/js/Tween.js');
        $this->view->headScript()->appendFile('/js/three/three.min.js');
        $this->view->headScript()->appendFile('/js/three/Detector.js');

        $this->view->headScript()->appendFile('/js/default.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/libs.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/chat/init.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/chat/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/chat/websocketMessage.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/chat/websocketSend.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/mapgenerator/mapgenerator.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/mapgenerator/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/mapgenerator/websocketMessage.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/mapgenerator/websocketSend.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/main/init.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/index.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/main.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/contact.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/create.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/editor.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/friends.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/game.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/over.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/join.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/messages.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/tutorial.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/load.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/halloffame.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/help.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/play.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/players.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/profile.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/setup.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/single.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/websocketMessage.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/main/websocketSend.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/new/join.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/new/setup.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/new/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/new/websocketMessage.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/new/websocketSend.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/help/help.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/help/helpModels.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/help/helpScene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/help/helpRenderer.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/editor/castleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/editor.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/models.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/gui.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/websocketSend.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/websocketMessage.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/battleModels.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/battleScene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/execute.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/game.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/unit.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/terrain.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/player.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/players.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/army.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/init.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/me.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/message.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/astar.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/gui.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/move.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/turn.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/sound.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/castleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/splitWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/statusWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/battleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/treasuryWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/statisticsWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/websocketMessage.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/websocketSend.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/common/castle.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/castles.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/renderer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ground.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameRenderer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameScene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameModels.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/field.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/fields.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruin.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruins.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/tower.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/towers.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/units.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/tutorial/tutorial.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/tutorial/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/tutorial/websocketMessage.js?v=' . $version);

        $this->view->sound();
        $this->view->title();
        $this->view->models();
        $this->view->translations();
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
