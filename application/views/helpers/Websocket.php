<?php

class Zend_View_Helper_Websocket extends Zend_View_Helper_Abstract
{
    public function websocket($identity)
    {
        $configWS = Zend_Registry::get('config')->websockets;

        $script = '
        WEB_SOCKET_DEBUG = true;
        var wsURL = "' . $configWS->aSchema . '://' . $configWS->aHost . '", wsPort = "' . $configWS->aPort . '",
  id = ' . $identity->playerId . ', accessKey = "' . $identity->accessKey . '", langId =  ' . Zend_Registry::get('id_lang') . ',
  playerName = "' . $identity->firstName . ' ' . $identity->lastName . '";
  ';

        $this->view->headScript()->appendScript($script);
    }
}
