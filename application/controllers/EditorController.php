<?php

class EditorController extends Game_Controller_Gui
{
    public function indexAction()
    {
        if ($this->_namespace->mapId) {
            unset($this->_namespace->mapId);
        }
        $mMap = new Application_Model_Map ();
        $this->view->mapList = $mMap->getPlayerMapList($this->_namespace->player['playerId']);
    }

    public function createAction()
    {
        $this->view->form = new Application_Form_Createmap ();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $mMap = new Application_Model_Map ();
                $mapId = $mMap->createMap($this->view->form->getValues(), $this->_namespace->player['playerId']);
                $this->_redirect($this->view->url(array('action' => 'edit', 'mapId' => $mapId)));
            }
        }
    }

    public function editAction()
    {
        $this->_helper->layout->setLayout('editor');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/editor.css?v=' . Zend_Registry::get('config')->version);
//        $this->view->headScript()->appendFile('/js/kinetic-v4.7.4.min.js');
        $this->view->headScript()->appendFile('http://d3lp1msu2r81bx.cloudfront.net/kjs/js/lib/kinetic-v5.0.1.min.js');
        $this->view->headScript()->appendFile('/js/editor.js?v=' . Zend_Registry::get('config')->version);

        $mMap = new Application_Model_Map($this->_request->getParam('mapId'));
        $this->view->map = $mMap->getMap();
    }

    public function testAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $mMapFields = new Application_Model_MapFields(1);

        $mMapper = new Application_Model_Mapper($mMapFields->getMapFields());
        $mMapper->generate();
        $im = $mMapper->getIm();

        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
    }
}

