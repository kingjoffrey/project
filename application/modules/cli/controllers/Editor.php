<?php

class EditorController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $db = $handler->getDb();
        $mMap = new Application_Model_Map (0, $db);

        $view = new Zend_View();
        $view->mapList = $mMap->getPlayerMapList($user->parameters['playerId']);
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'editor',
            'action' => 'index',
            'data' => $view->render('editor/index.phtml')
        );

        $handler->sendToUser($user, $token);
    }

    function create(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $view = new Zend_View();
        $view->formCreate = new Application_Form_Createmap ();
        $view->formCreate->setView($view);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'editor',
            'action' => 'create',
            'data' => $view->render('editor/create.phtml')
        );

        $handler->sendToUser($user, $token);

//        $this->_view->formIsValid = false;
//        if ($this->_request->isPost()) {
//            if ($this->_view->form->isValid($this->_request->getPost())) {
//                $this->_view->formIsValid = true;
//                $mMap = new Application_Model_Map ();
//                $this->_view->mapId = $mMap->createMap($this->_view->form->getValues(), Zend_Auth::getInstance()->getIdentity()->playerId);
//
//                $mSide = new Application_Model_Side();
//
//                $mMapPlayers = new Application_Model_MapPlayers($this->_view->mapId);
//                $mMapPlayers->create($mSide->getWithLimit($this->_request->getParam('maxPlayers')), $this->_view->mapId);
//
//                $this->_view->mapSize = $this->_request->getParam('mapSize');
//
//                $this->_helper->layout->setLayout('empty');
//
//                $this->_view->headScript()->appendFile('/js/mapgenerator/init.js?v=' . Zend_Registry::get('config')->version);
//                $this->_view->headScript()->appendFile('/js/mapgenerator/diamondsquare.js?v=' . Zend_Registry::get('config')->version);
//                $this->_view->headScript()->appendFile('/js/mapgenerator/mapgenerator.js?v=' . Zend_Registry::get('config')->version);
//                $this->_view->headScript()->appendFile('/js/mapgenerator/websocket.js?v=' . Zend_Registry::get('config')->version);
//            }
//        }
    }

    function edit(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $layout = new Zend_Layout();
        $layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts');
        $layout->setLayout('editor');

        $token = array(
            'type' => 'editor',
            'action' => 'edit',
            'data' => $layout->render()
        );

        $handler->sendToUser($user, $token);
    }
}