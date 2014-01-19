<?php

abstract class Game_Controller_Action extends Zend_Controller_Action
{

    protected $_namespace;

    public function init()
    {
        parent::init();

        $this->_namespace = Game_Namespace::getNamespace(); // default namespace
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        // Konstruktor klasy nadrzędnej
        parent::__construct($request, $response, $invokeArgs);
    }

}
