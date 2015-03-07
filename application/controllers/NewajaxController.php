<?php

class NewajaxController extends Coret_Controller_Authorized
{

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function refreshAction()
    {
        $mGame = new Application_Model_Game();
        $response = $mGame->getOpen();
        echo Zend_Json::encode($response);
    }

    public function nopAction()
    {
        $form = new Application_Form_NumberOfPlayers(array('mapId' => $this->_request->getParam('mapId')));
        echo Zend_Json::encode($form->__toString());
    }
}
