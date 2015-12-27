<?php

class Coret_Model_Mailtransport extends Zend_Mail_Transport_Smtp
{

    public function __construct()
    {
        $smtp = Zend_Registry::get('config')->smtp;
        if (!$smtp) {
            throw new Zend_Exception('SMTP not enabled in application.ini');
        }
        $config = array('auth' => $smtp->auth,
            'username' => $smtp->username,
            'password' => $smtp->password,
            'ssl' => $smtp->ssl,
            'port' => $smtp->port
        );
        parent::__construct($smtp->server, $config);
    }

}

