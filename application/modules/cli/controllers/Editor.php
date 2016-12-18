<?php

class EditorController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $db = $handler->getDb();
        $mMap = new Application_Model_Map (0, $db);
        $token = array(
            'type' => 'editor',
            'action' => 'index',
            'data' => $mMap->getPlayerMapList($user->parameters['playerId'])
        );
        $handler->sendToUser($user, $token);
    }

    function create()
    {
        $this->_view->form = new Application_Form_Createmap ();
        $this->_view->formIsValid = false;
        if ($this->_request->isPost()) {
            if ($this->_view->form->isValid($this->_request->getPost())) {
                $this->_view->formIsValid = true;
                $mMap = new Application_Model_Map ();
                $this->_view->mapId = $mMap->createMap($this->_view->form->getValues(), Zend_Auth::getInstance()->getIdentity()->playerId);

                $mSide = new Application_Model_Side();

                $mMapPlayers = new Application_Model_MapPlayers($this->_view->mapId);
                $mMapPlayers->create($mSide->getWithLimit($this->_request->getParam('maxPlayers')), $this->_view->mapId);

                $this->_view->mapSize = $this->_request->getParam('mapSize');

                $this->_helper->layout->setLayout('empty');

                $this->_view->headScript()->appendFile('/js/mapgenerator/init.js?v=' . Zend_Registry::get('config')->version);
                $this->_view->headScript()->appendFile('/js/mapgenerator/diamondsquare.js?v=' . Zend_Registry::get('config')->version);
                $this->_view->headScript()->appendFile('/js/mapgenerator/mapgenerator.js?v=' . Zend_Registry::get('config')->version);
                $this->_view->headScript()->appendFile('/js/mapgenerator/websocket.js?v=' . Zend_Registry::get('config')->version);
            }
        }
    }

    function edit()
    {

    }
}