<?php

class Zend_View_Helper_Websocket extends Zend_View_Helper_Abstract
{
    public function websocket()
    {
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/flash-bridge/swfobject.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/flash-bridge/web_socket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/websocket.js');

        $script = '
        WEB_SOCKET_SWF_LOCATION = "/js/flash-bridge/WebSocketMain.swf";
        WEB_SOCKET_DEBUG = true;
        var wsURL = "' . Zend_Registry::get('config')->websockets->aSchema . '://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort . '",
        ws = null';

        $this->view->headScript()->appendScript($script);
    }
}
