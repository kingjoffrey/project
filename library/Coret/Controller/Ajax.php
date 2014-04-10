<?php

abstract class Coret_Controller_Ajax extends Zend_Controller_Action
{

    public function init()
    {
        parent::init();

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
    }
}