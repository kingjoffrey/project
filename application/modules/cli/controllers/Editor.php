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

    function create(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $view->formCreate = new Application_Form_Createmap ();
        $view->formCreate->setView($view);

        if (isset($dataIn['name']) && $view->formCreate->isValid($dataIn)) {
            $db = $handler->getDb();

            $mMap = new Application_Model_Map (0, $db);
            $mapId = $mMap->create($view->formCreate->getValues(), $user->parameters['playerId']);

            $mSide = new Application_Model_Side(0, $db);

            $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
            $mMapPlayers->create($mSide->getWithLimit($dataIn['maxPlayers']));

            $layout = new Zend_Layout();
            $layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts');
            $layout->setLayout('editor');

            $token = array(
                'type' => 'editor',
                'action' => 'generate',
                'mapSize' => $dataIn['mapSize'],
                'mapId' => $mapId,
                'data' => $layout->render()
            );
        } else {
            $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

            $token = array(
                'type' => 'editor',
                'action' => 'create',
                'data' => $view->render('editor/create.phtml')
            );
        }
        $handler->sendToUser($user, $token);
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

    function delete(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['id'])) {
            return;
        }

        $db = $handler->getDb();

        $mMap = new Application_Model_Map ($dataIn['id'], $db);

        if ($mMap->deleteNotPublished($user->parameters['playerId'])) {
            $token = array(
                'type' => 'editor',
                'action' => 'delete',
                'id' => $dataIn['id']
            );

            $handler->sendToUser($user, $token);
        }
    }
}