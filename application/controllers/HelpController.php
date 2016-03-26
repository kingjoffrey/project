<?php

class HelpController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->models();

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/help.css?v=' . $this->_version);

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/init.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/help.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/websocket.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/websocketMessage.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/websocketSend.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/three/three.js');
        $this->view->headScript()->appendFile('/js/three/Detector.js');
        $this->view->headScript()->appendFile('/js/geometries/TextGeometry.js');
        $this->view->headScript()->appendFile('/js/utils/FontUtils.js');
        $this->view->headScript()->appendFile('/fonts/helvetiker_regular.typeface.js');


        $this->view->headScript()->appendFile('/js/common/scene.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $this->_version);

        $this->view->helpMenu();
    }

}

