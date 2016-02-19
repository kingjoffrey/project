<?php

class EditorController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $mMap = new Application_Model_Map ();
        $this->view->mapList = $mMap->getPlayerMapList(Zend_Auth::getInstance()->getIdentity()->playerId);
    }

    public function createAction()
    {
        $this->view->form = new Application_Form_Createmap ();
        $this->view->formIsValid = false;
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $this->viev->formIsValid = true;
                $mMap = new Application_Model_Map ();
                $this->view->mapId = $mMap->createMap($this->view->form->getValues(), Zend_Auth::getInstance()->getIdentity()->playerId);

                $mSide = new Application_Model_Side();

                $mMapPlayers = new Application_Model_MapPlayers($this->view->mapId);
                $mMapPlayers->create($mSide->getWithLimit($this->_request->getParam('maxPlayers')), $this->view->mapId);

                $this->view->mapSize = $this->_request->getParam('mapSize');

                $this->_helper->layout->setLayout('generatemap');

                $this->view->headScript()->appendFile('/js/mapgenerator/init.js?v=' . Zend_Registry::get('config')->version);
                $this->view->headScript()->appendFile('/js/mapgenerator/diamondsquare.js?v=' . Zend_Registry::get('config')->version);
                $this->view->headScript()->appendFile('/js/mapgenerator/mapGenerator.js?v=' . Zend_Registry::get('config')->version);
                $this->view->headScript()->appendFile('/js/mapgenerator/websocket.js?v=' . Zend_Registry::get('config')->version);
            }
        }
    }

    public function editAction()
    {
        $this->view->models();

        $version = Zend_Registry::get('config')->version;

        $this->_helper->layout->setLayout('editor');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/editor.css?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile('/js/jquery.mousewheel.min.js');
        $this->view->headScript()->appendFile('/js/Tween.js');
        $this->view->headScript()->appendFile('/js/three/three.js');
        $this->view->headScript()->appendFile('/js/three/Detector.js');
        $this->view->headScript()->appendFile('/js/three/Mirror.js');
        $this->view->headScript()->appendFile('/js/three/WaterShader.js');
        $this->view->headScript()->appendFile('/js/geometries/TextGeometry.js');
        $this->view->headScript()->appendFile('/js/utils/FontUtils.js');
        $this->view->headScript()->appendFile('/fonts/helvetiker_regular.typeface.js');

        $this->view->headScript()->appendFile('/js/editor/message.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/castleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/editor.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/models.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/gui.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/editor/players.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/player.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/units.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/unit.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/common/castle.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/castles.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/scene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ground.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/field.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/fields.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruin.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruins.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/tower.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/towers.js?v=' . $version);

        $mapId = $this->_request->getParam('mapId');

        $mMap = new Application_Model_Map($mapId);
        $this->view->map = $mMap->getMap(Zend_Auth::getInstance()->getIdentity()->playerId);
    }

    public function testAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $mMapFields = new Application_Model_MapFields(1);

        $mMapper = new Application_Model_Mapper($mMapFields->getMapFields());
        $mMapper->generate();
        $im = $mMapper->getIm();

        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
    }
}

