<?php

abstract class Coret_Controller_Authenticate extends Zend_Controller_Action
{
    protected $_login;
    protected $_password;
    protected $_authAdapter;
    protected $_auth;

    public function init()
    {
        $this->_auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));
    }

    public function indexAction()
    {
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $this->_authAdapter = $this->getAuthAdapter($this->view->form->getValues());
                $result = $this->_auth->authenticate($this->_authAdapter);
                if ($result->isValid()) {
                    $this->handleAuthenticated();
                } else {
                    $this->view->form->setDescription($this->view->translate('Incorrect login details'));
                }
            }
        }
    }

    protected function handleAuthenticated()
    {
        $identity = $this->_authAdapter->getResultRowObject(array('login', 'user_id', 'guild_id', 'type'));
        $this->_auth->getStorage()->write($identity);

        if ($this->_request->getParam('rememberMe')) {
            Zend_Session::rememberMe(Zend_Registry::get('config')->rememberMeTime);
        } else {
            Zend_Session::forgetMe();
        }

        $this->redirectAuthenticated();
    }

    protected function redirectAuthenticated()
    {
        $this->redirect($this->view->url(array('controller' => 'index')));
    }

    protected function logout()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $this->_auth->clearIdentity();
//        Zend_Session::destroy(true);
    }

}

