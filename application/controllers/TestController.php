<?php

class TestController extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';

    public function indexAction()
    {
        $version = Zend_Registry::get('config')->version;

        $this->_helper->layout->setLayout('test');
        $this->view->jquery();

//        $this->view->Websocket($this->_auth->getIdentity());


        $this->view->headLink()->appendStylesheet('/css/test.css?v=' . $version);
        $this->view->headScript()->appendFile('/js/three/three.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/three/Detector.js');
        $this->view->headScript()->appendFile('/js/Tween.js');


        $this->view->headScript()->appendFile('/js/libs.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/test3.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/common/field.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/fields.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ground.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameRenderer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameScene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameModels.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/renderer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/picker.js?v=' . $version);

        $mMapFields = new Application_Model_MapFields(321);
        $Fields = new Cli_Model_Fields($mMapFields->getMapFields());
        $this->view->fields = $Fields->toArray();

        $this->view->models();
    }
}