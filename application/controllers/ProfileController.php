<?php

class ProfileController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $mPlayer = new Application_Model_Player();
        $player = $mPlayer->getPlayer($this->_playerId);

        $this->view->formPlayer = new Application_Form_Player();
        $this->view->formPlayer->populate($player);

        $this->view->formEmail = new Application_Form_Email();
        $this->view->formEmail->populate($player);

        $this->view->formPassword = new Application_Form_Password();

        if (!$this->_request->isPost()) {
            return;
        }

        $data = $this->_request->getPost();
        unset($data['submit']);
        $valid = false;

        if ($this->_request->getParam('password')) {
            if ($this->view->formPassword->isValid($data)) {
                $valid = true;
            }
            unset($data['repeatPassword']);
            $data['password'] = md5($data['password']);
        } elseif ($this->_request->getParam('login')) {
            if ($this->view->formEmail->isValid($data)) {
                $valid = true;
            }
        } else {
            if ($this->view->formPlayer->isValid($data)) {
                $valid = true;
            }
        }

        if ($valid) {
            $mPlayer->updatePlayer($data, $this->_playerId);
            $this->redirect('/' . $this->_request->getParam('lang') . '/profile');
        }
    }

    public function showAction()
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

