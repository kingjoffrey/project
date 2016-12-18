<?php

class PlayersController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $view = new Zend_View();
        $view->searchForm = new Application_Form_Search();
        $view->searchForm->setView($view);

//        if ($this->_request->isPost()) {
//            if ($this->view->searchForm->isValid($this->_request->getPost())) {
//                $mPlayer = new Application_Model_Player();
//                $this->view->searchResults = $mPlayer->search($this->view->searchForm->getValue('search'));
//            }
//        }

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'players',
            'action' => 'index',
            'data' => $view->render('players/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }

    function add()
    {
        if ($this->_request->getParam('friendId')) {
            $mFriend = new Application_Model_Friends();
            $mFriend->create(Zend_Auth::getInstance()->getIdentity()->playerId, $this->_request->getParam('friendId'));
        }
//        $this->redirect('/' . $this->_request->getParam('lang') . '/players');
    }
}