<?php

class LoginController extends Coret_Controller_AuthenticateFrontend
{
    protected $_authTableName = 'player';
    protected $_identityArray = array('login', 'firstName', 'lastName', 'playerId');

    private function html()
    {
        $this->_helper->layout->setLayout('login');

        $version = Zend_Registry::get('config')->version;

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . $version);

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/default.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/libs.js?v=' . $version);

        $this->view->title();
        $this->view->Version();
    }

    public function indexAction()
    {
        $mLanguage = new Application_Model_Language();

        $this->view->langForm = new Application_Form_Language(array('langList' => $mLanguage->get()));
        $this->view->form = new Application_Form_Auth();

        parent::indexAction();

        $this->html();
    }

    public function anonymousAction()
    {
        $mHNG = new Cli_Model_HeroNameGenerator();
        $heroName = $mHNG->generateHeroName();

        $playerName = explode(' ', $heroName);

        $anonymous = array(
            'login' => md5($heroName . rand()),
            'password' => 'abc' . rand()
        );

        $data = array(
            'firstName' => $playerName[0],
            'lastName' => $playerName[1],
            'login' => $anonymous['login'],
            'password' => md5($anonymous['password'])
        );

        $mPlayer = new Application_Model_Player();
        if ($playerId = $mPlayer->createPlayer($data)) {
            $this->_request->setParam('rememberMe', 1);


            $mHero = new Application_Model_Hero($playerId);
            $mHero->createHero($heroName);

            $this->_authAdapter = $this->getAuthAdapter($anonymous);
            $this->_auth->authenticate($this->_authAdapter);
            $this->handleAuthenticated();
        }
    }

    public function registrationAction()
    {
        $this->html();

        $form = new Application_Form_Registration();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $mHNG = new Cli_Model_HeroNameGenerator();

                $heroName = $mHNG->generateHeroName();

                $playerName = explode(' ', $heroName);

                $data = array(
                    'firstName' => $playerName[0],
                    'lastName' => $playerName[1],
                    'login' => $this->_request->getParam('login'),
                    'password' => md5($this->_request->getParam('password'))
                );

                $mPlayer = new Application_Model_Player();
                if ($playerId = $mPlayer->createPlayer($data)) {
                    $mHero = new Application_Model_Hero($playerId);
                    $mHero->createHero($heroName);

                    $this->_authAdapter = $this->getAuthAdapter($form->getValues());
                    $this->_auth->authenticate($this->_authAdapter);
                    $this->handleAuthenticated();
                }
            } else {
                $this->view->form = $form;
            }
        } else {
            $this->view->form = $form;
        }
    }

    protected function writeIdentity()
    {
        $identity = $this->_authAdapter->getResultRowObject($this->_identityArray);

        $mWebSocket = new Application_Model_Websocket($identity->playerId);
        $identity->accessKey = $mWebSocket->generateKey();
        $mWebSocket->create($identity->accessKey);

        $this->_auth->getStorage()->write($identity);
    }
}

