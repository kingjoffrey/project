<?php

class Application_Form_Creategame extends Zend_Form
{

    public function init()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Select(array('name' => 'mapId', 'label' => $adapter->translate('Select map') . ':', 'opt' => $this->_attribs['mapsList']));
        $this->addElements($f->getElements());

        $this->addElement('submit', 'submit', array('label' => $adapter->translate('Create game')));
    }

}

