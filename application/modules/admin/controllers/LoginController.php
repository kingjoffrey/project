<?php

class Admin_LoginController extends Coret_Controller_AuthenticateBackend
{
    protected $_authTableName = 'player';
    protected $_identityArray = array('login', 'playerId');

    protected function writeIdentity()
    {
        $result = $this->_authAdapter->getResultRowObject($this->_identityArray);
        $result->user_id = $result->playerId;
        $this->_auth->getStorage()->write($result);
    }
}

