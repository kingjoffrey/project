<?php

class LoginController extends Coret_Controller_AuthenticateFrontend
{
    protected $_authTableName = 'player';
    protected $_identityArray = array('login', 'firstName', 'lastName', 'playerId');
    protected $_facebookDatabaseName = 'fbId';

    private function html()
    {
        $this->_helper->layout->setLayout('login');

        $version = Zend_Registry::get('config')->version;

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . $version);

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/login.js?v=' . $version);
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

    public function registrationAction()
    {
        $this->html();

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
                    $mHero = new Application_Model_Hero($playerId);
                    $mHNG = new Cli_Model_HeroNameGenerator();
                    $mHero->createHero($mHNG->generateHeroName());

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

    protected function handleFacebookUser($userProfile)
    {
        $facebookId = $userProfile->getId();

        $this->_authAdapter = $this->getAuthAdapterFacebook($facebookId);
        $result = $this->_auth->authenticate($this->_authAdapter);

        if ($result->isValid()) {
            $this->handleAuthenticated();
        } elseif ($facebookId) {
            if ($firstName = $userProfile->getFirstName() || $lastName = $userProfile->getLastName()) {
                $data = array(
                    'fbId' => $facebookId,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                );
            } else {
                $data = array(
                    'fbId' => $facebookId,
                    'firstName' => $userProfile->getName()
                );
            }

            $mPlayer = new Application_Model_Player();
            if ($playerId = $mPlayer->createPlayer($data)) {
                $mHero = new Application_Model_Hero($playerId);
                $mHNG = new Cli_Model_HeroNameGenerator();
                $mHero->createHero($mHNG->generateHeroName());

                $this->_authAdapter = $this->getAuthAdapterFacebook($facebookId);
                $this->_auth->authenticate($this->_authAdapter);
                $this->handleAuthenticated();
            }
        } else {
            $this->view->form->setDescription($this->view->translate('Incorrect login details'));
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

