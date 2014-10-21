<?php

class LoginController extends Coret_Controller_AuthenticateFrontend
{
    protected $_authTableName = 'player';
    protected $_identityArray = array('login', 'firstName', 'lastName', 'playerId');

    public function indexAction()
    {
        $this->view->form = new Application_Form_Auth();
        parent::indexAction();

        $this->_helper->layout->setLayout('login');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/login.css');

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/login.js?v=' . Zend_Registry::get('config')->version);

        $this->view->title();
        $this->view->Version();
    }

    public function registrationAction()
    {
        $form = new Application_Form_Registration();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $mPlayer = new Application_Model_Player();
                $data = array(
                    'firstName' => $this->_request->getParam('firstName'),
                    'lastName' => $this->_request->getParam('lastName'),
                    'login' => $this->_request->getParam('login'),
                    'password' => md5($this->_request->getParam('password'))
                );
                $playerId = $mPlayer->createPlayer($data);
                if ($playerId) {
                    $modelHero = new Application_Model_Hero($playerId);
                    $modelHero->createHero();
                    $this->_namespace->player = $mPlayer->getPlayer($playerId);
                    $this->redirect($this->view->url(array('controller' => 'index', 'action' => null)));
                }
            }
        }
        $this->view->form = $form;
    }

    protected function handleFacebookUser($userProfile)
    {
        $facebookId = $userProfile->getId();

        $mPlayer = new Application_Model_Player();
        if ($playerId = $mPlayer->hasFacebookId($facebookId)) {
            $this->_namespace->player = $mPlayer->getPlayer($playerId);
            $this->redirect($this->view->url(array('controller' => 'index', 'action' => null)));
        } else {
            $data = array(
                'fbId' => $facebookId,
                'firstName' => $userProfile->getFirstName(),
                'lastName' => $userProfile->getLastName(),
            );
            if ($playerId = $mPlayer->createPlayer($data)) {
                $modelHero = new Application_Model_Hero($playerId);
                $modelHero->createHero();
                $this->_namespace->player = $mPlayer->getPlayer($playerId);
                $this->redirect($this->view->url(array('controller' => 'index', 'action' => null)));
            }
        }
    }
}

