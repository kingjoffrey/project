<?php

abstract class Coret_Controller_AuthenticateBackend extends Coret_Controller_Authenticate
{
    public function indexAction()
    {
        $this->view->form = new Admin_Form_Login();

        parent::indexAction();

        $this->_helper->layout->setLayout('admin_login');
        $this->view->headLink()->prependStylesheet('/css/core-t_admin_login.css');
        $this->view->copyright();
    }

    public function logoutAction()
    {
        parent::logout();
        $this->redirect('/admin/login');
    }

    protected function getAuthAdapter($params)
    {
        $authAdapter = new Zend_Auth_Adapter_DbTable(
            Zend_Db_Table_Abstract::getDefaultAdapter(),
            $this->_authTableName,
            $this->_loginDatabaseName,
            $this->_passwordDatabaseName,
            'MD5(?) AND active = 1 AND type > 0'
        );
        $authAdapter->setIdentity($params[$this->_loginFormName]);
        $authAdapter->setCredential($params[$this->_passwordFormName]);
        return $authAdapter;
    }

}

