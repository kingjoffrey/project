<?php

class NewajaxxController extends Coret_Controller_Authorized //todo: usunąć ten plik?
{

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function nopAction()
    {
        $form = new Application_Form_NumberOfPlayers(array('mapId' => $this->_request->getParam('mapId')));
        echo Zend_Json::encode($form->__toString());
    }
}
