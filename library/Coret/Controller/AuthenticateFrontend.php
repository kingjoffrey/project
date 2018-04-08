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
}

