<?php

class Zend_View_Helper_Websocket extends Zend_View_Helper_Abstract
{
    public function websocket($identity)
    {
        $version = Zend_Registry::get('config')->version;

//        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/flash-bridge/swfobject.js');
//        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/flash-bridge/web_socket.js');

        $this->view->headScript()->appendFile('/js/privatechat/privatechat.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/libs.js?v=' . $version);

        $configWS = Zend_Registry::get('config')->websockets;

        //        WEB_SOCKET_SWF_LOCATION = "/js/flash-bridge/WebSocketMain.swf";

        $script = '
        WEB_SOCKET_DEBUG = true;
        var wsURL = "' . $configWS->aSchema . '://' . $configWS->aHost . ':' . $configWS->aPort . '",
  id = ' . $identity->playerId . ', accessKey = "' . $identity->accessKey . '", langId =  ' . Zend_Registry::get('id_lang') . ',
  playerName = "' . $identity->firstName . ' ' . $identity->lastName . '";
  ';

        $this->view->headScript()->appendScript($script);
    }
}
