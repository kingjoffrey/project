<?php

class LoginController extends Coret_Controller_AuthenticateFrontend
{
    protected $_authTableName = 'player';
    protected $_identityArray = array('login', 'firstName', 'lastName', 'playerId');
    protected $_facebookDatabaseName = 'fbId';

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
        $this->_helper->layout->setLayout('login');

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/login.css');

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/login.js?v=' . Zend_Registry::get('config')->version);

        $this->view->title();
        $this->view->Version();

        $form = new Application_Form_Registration();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $data = array(
                    'firstName' => $this->_request->getParam('firstName'),
                    'lastName' => $this->_request->getParam('lastName'),
                    'login' => $this->_request->getParam('login'),
                    'password' => md5($this->_request->getParam('password'))
                );
                $mPlayer = new Application_Model_Player();
                if ($playerId = $mPlayer->createPlayer($data)) {
                    $modelHero = new Application_Model_Hero($playerId);
                    $modelHero->createHero();
                    $this->_authAdapter = $this->getAuthAdapter($form->getValues());
                    $this->_auth->authenticate($this->_authAdapter);
                    $this->handleAuthenticated();
                }
            }
        }
        $this->view->form = $form;
    }

    protected function handleFacebookUser($userProfile)
    {
        $facebookId = $userProfile->getId();

        $this->_authAdapter = $this->getAuthAdapterFacebook($facebookId);
        $result = $this->_auth->authenticate($this->_authAdapter);

        if ($result->isValid()) {
            $this->handleAuthenticated();
        } elseif ($facebookId) {
            $data = array(
                'fbId' => $facebookId,
                'firstName' => $userProfile->getFirstName(),
                'lastName' => $userProfile->getLastName(),
            );
            $mPlayer = new Application_Model_Player();
            if ($playerId = $mPlayer->createPlayer($data)) {
                $modelHero = new Application_Model_Hero($playerId);
                $modelHero->createHero();
                $this->handleAuthenticated();
            }
        } else {
            $this->view->form->setDescription($this->view->translate('Incorrect login details'));
        }
    }


    protected function writeAuthentication()
    {
        $identity = $this->_authAdapter->getResultRowObject($this->_identityArray);

        $mWebSocket = new Application_Model_Websocket($identity->playerId);
        $identity->accessKey = $mWebSocket->generateKey();
        $mWebSocket->init('chat', $identity->accessKey);

        $this->_auth->getStorage()->write($identity);
    }
}

