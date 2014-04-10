<?php

abstract class Coret_Controller_AuthenticateFrontend extends Coret_Controller_Authenticate
{
    protected function getAuthAdapter($params)
    {
        $authAdapter = new Zend_Auth_Adapter_DbTable(
            Zend_Db_Table_Abstract::getDefaultAdapter(),
            'users',
            'login',
            'password',
            'MD5(?) AND active = 1'
        );
        $authAdapter->setIdentity($params[$this->_login]);
        $authAdapter->setCredential($params[$this->_password]);
        return $authAdapter;
    }

}

