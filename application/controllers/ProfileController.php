<?php

class ProfileController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $mPlayer = new Application_Model_Player();
        $player = $mPlayer->getPlayer(Zend_Auth::getInstance()->getIdentity()->playerId);

        $this->view->formPlayer = new Application_Form_Player();
        $this->view->formPlayer->populate($player);
        $this->view->formPlayer->addElement('submit', 'submit', array('label' => $this->view->translate('Submit')));

        $this->view->formPassword = new Application_Form_Password();
        $this->view->formPassword->addElement('submit', 'submit', array('label' => $this->view->translate('Submit')));

        if ($this->_request->isPost()) {
            $data = $this->_request->getPost();
            unset($data['submit']);
            $valid = false;

            if ($this->_request->getParam('password')) {
                if ($this->view->formPassword->isValid($data)) {
                    $valid = true;
                }
                unset($data['repeatPassword']);
                $data['password'] = md5($data['password']);
            } else {
                if ($this->view->formPlayer->isValid($data)) {
                    $valid = true;
                }
            }

            if ($valid) {
                $mPlayer->updatePlayer($data, Zend_Auth::getInstance()->getIdentity()->playerId);
                $this->redirect('/profile');
            }
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

