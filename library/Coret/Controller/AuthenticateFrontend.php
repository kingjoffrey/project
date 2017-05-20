<?php

abstract class Coret_Controller_AuthenticateFrontend extends Coret_Controller_Authenticate
{
    protected function getAuthAdapter($params)
    {
        $authAdapter = new Zend_Auth_Adapter_DbTable(
            Zend_Db_Table_Abstract::getDefaultAdapter(),
            $this->_authTableName,
            $this->_loginDatabaseName,
            $this->_passwordDatabaseName,
            'MD5(?) AND active = 1'
        );

        $authAdapter->setIdentity($params[$this->_loginFormName]);
        $authAdapter->setCredential($params[$this->_passwordFormName]);

        return $authAdapter;
    }

    protected function getAuthAdapterFacebook($facebookId)
    {
        $authAdapter = new Zend_Auth_Adapter_DbTable(
            Zend_Db_Table_Abstract::getDefaultAdapter(),
            $this->_authTableName,
            $this->_facebookDatabaseName,
            $this->_facebookDatabaseName,
            '? AND active = 1'
        );

        $authAdapter->setIdentity($facebookId);
        $authAdapter->setCredential($facebookId);

        return $authAdapter;
    }

    public function indexAction()
    {
        Facebook\FacebookSession::setDefaultApplication(Zend_Registry::get('config')->facebook->appId, Zend_Registry::get('config')->facebook->appPassword);
        $helper = new Facebook\FacebookRedirectLoginHelper($this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost() . $this->view->url(array('action' => 'facebook')));
        $this->view->loginUrl = $helper->getLoginUrl();

        parent::indexAction();
    }

    public function facebookAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        Facebook\FacebookSession::setDefaultApplication(Zend_Registry::get('config')->facebook->appId, Zend_Registry::get('config')->facebook->appPassword);
        $helper = new Facebook\FacebookRedirectLoginHelper($this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost() . $this->view->url(array('action' => 'facebook')));
        try {
            $session = $helper->getSessionFromRedirect();
        } catch (Facebook\FacebookRequestException $ex) {
            // When Facebook returns an error
            echo $ex->getMessage();
            $l = new Coret_Model_Logger('www');
            $l->log($ex);
        } catch (\Exception $ex) {
            // When validation fails or other local issues
            $l = new Coret_Model_Logger('www');
            $l->log($ex);
            $this->redirect($this->view->url(array('action' => null)));
        }

        if ($session) {
            try {
                $request = new Facebook\FacebookRequest($session, 'GET', '/me');
                $response = $request->execute();
                $this->handleFacebookUser($response->getGraphObject(Facebook\GraphUser::className()));
            } catch (FacebookRequestException $e) {
                $l = new Coret_Model_Logger('www');
                $l->log('Exception occured, code: ' . $e->getCode());
                $l->log(' with message: ' . $e->getMessage());
            }
        } else {
            $this->redirect($this->view->url(array('controller' => 'login', 'action' => null)));
        }
    }

    protected function handleFacebookUser($userProfile)
    {
    }
}

