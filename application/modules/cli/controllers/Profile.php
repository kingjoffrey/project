<?php

class ProfileController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $db = $handler->getDb();
        $mPlayer = new Application_Model_Player($db);
        $player = $mPlayer->getPlayer($user->parameters['playerId']);

        $view = new Zend_View();

        $view->formPlayer = new Application_Form_Player();
        $view->formPlayer->populate($player);
        $view->formPlayer->setView($view);

        $view->formEmail = new Application_Form_Email();
        $view->formEmail->populate($player);
        $view->formEmail->setView($view);

        $view->formPassword = new Application_Form_Password();
        $view->formPassword->setView($view);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'profile',
            'action' => 'index',
            'data' => $view->render('profile/index.phtml')
        );
        $handler->sendToUser($user, $token);

//        if (!$this->_request->isPost()) {
//            return;
//        }
//
//        $data = $this->_request->getPost();
//        unset($data['submit']);
//        $valid = false;
//
//        if ($this->_request->getParam('password')) {
//            if ($view->formPassword->isValid($data)) {
//                $valid = true;
//            }
//            unset($data['repeatPassword']);
//            $data['password'] = md5($data['password']);
//        } elseif ($this->_request->getParam('login')) {
//            if ($view->formEmail->isValid($data)) {
//                $valid = true;
//            }
//        } else {
//            if ($view->formPlayer->isValid($data)) {
//                $valid = true;
//            }
//        }
//
//        if ($valid) {
//            $mPlayer->updatePlayer($data, $this->_playerId);
//            $this->redirect('/' . $this->_request->getParam('lang') . '/profile');
//        }
    }

    function show(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        if (!$playerId = $this->_request->getParam('playerId')) {
            return;
        }
        $this->view->headScript()->appendFile('/js/profile.js?v=' . Zend_Registry::get('config')->version);
        $mPlayer = new Application_Model_Player();
        $this->view->player = $mPlayer->getPlayer($playerId);

        $mGameScore = new Application_Model_GameScore();
        $this->view->playerScores = $mGameScore->getPlayerScores($playerId);
    }
}