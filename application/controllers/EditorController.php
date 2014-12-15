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
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $mMap = new Application_Model_Map ();
                $mapId = $mMap->createMap($this->view->form->getValues(), Zend_Auth::getInstance()->getIdentity()->playerId);
                $this->redirect($this->view->url(array('action' => 'edit', 'mapId' => $mapId)));
            }
        }
    }

    public function editAction()
    {
        $this->_helper->layout->setLayout('editor');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/editor.css?v=' . Zend_Registry::get('config')->version);

//        $this->view->headScript()->appendFile('/js/kinetic-v4.7.4.min.js');
        $this->view->headScript()->appendFile('http://d3lp1msu2r81bx.cloudfront.net/kjs/js/lib/kinetic-v5.0.1.min.js');
        $this->view->Websocket();
        $this->view->headScript()->appendFile('/js/editor/castle.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/editor/diamondsquare.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/editor/editor.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/editor/gui.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/editor/init.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/editor/websocket.js?v=' . Zend_Registry::get('config')->version);

        $mapId = $this->_request->getParam('mapId');

        $mMap = new Application_Model_Map($mapId);
        $this->view->map = $mMap->getMap(Zend_Auth::getInstance()->getIdentity()->playerId);

        $mWebSocket = new Application_Model_Websocket(Zend_Auth::getInstance()->getIdentity()->playerId);
        $this->view->websocket = $mWebSocket->connect('editor');
        $this->view->playerId = Zend_Auth::getInstance()->getIdentity()->playerId;

        $mMapCastles = new Application_Model_MapCastles($mapId);
        $this->view->mapCastles = $mMapCastles->getMapCastles();
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

