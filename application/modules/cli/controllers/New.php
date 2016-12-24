<?php

class NewController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        if (!isset($dataIn['mapId'])) {
            $dataIn['mapId'] = null;
        }

        $view->form = new Application_Form_Creategame(array(
            'mapId' => $dataIn['mapId'],
            'db' => $db
        ));
        $view->form->setView($view);

//        if ($this->view->form->isValid($this->_request->getPost())) {
//            $modelGame = new Application_Model_Game ();
//            $gameId = $modelGame->createGame($this->_request->getParams(), $this->_playerId);
//            $this->redirect('/' . $this->_request->getParam('lang') . '/setup/index/gameId/' . $gameId);
//        }

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'new',
            'action' => 'index',
            'data' => $view->render('new/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}