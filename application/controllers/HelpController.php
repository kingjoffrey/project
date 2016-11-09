<?php

class HelpController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->models();

        $this->view->headLink()->appendStylesheet('/css/help.css?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/three/three.js');
        $this->view->headScript()->appendFile('/js/three/Detector.js');

        $this->view->headScript()->appendFile('/js/help/init.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/help.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/helpModels.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/helpScene.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/helpRenderer.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/websocket.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/websocketMessage.js?v=' . $this->_version);
        $this->view->headScript()->appendFile('/js/help/websocketSend.js?v=' . $this->_version);

        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $this->_version);

        $this->view->helpMenu();
    }

}

