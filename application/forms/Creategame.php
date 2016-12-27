<?php

class Application_Form_Creategame extends Zend_Form
{

    public function init()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Select(array('name' => 'mapId', 'label' => $adapter->translate('Select map') . ':', 'opt' => $this->_attribs['mapsList']));
        $this->addElements($f->getElements());

        $f = new Application_Form_NumberOfPlayers(array('numberOfPlayers' => $this->_attribs['numberOfPlayers']));
        $this->addElements($f->getElements());

        $timeLimits = Application_Model_Limit::timeLimits();

        $f = new Coret_Form_Select(array('name' => 'timeLimit', 'label' => $adapter->translate('Select time limit') . ':', 'opt' => $timeLimits));
        $this->addElements($f->getElements());

        $f = new Coret_Form_Number(
            array(
                'name' => 'turnsLimit',
                'label' => $adapter->translate('Turns limit') . ':',
                'value' => 0,
                'required' => true
            )
        );
        $this->addElements($f->getElements());

        $turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $f = new Coret_Form_Select(array('name' => 'turnTimeLimit', 'label' => $adapter->translate('Select time limit per turn') . ':', 'opt' => $turnTimeLimit));
        $this->addElements($f->getElements());

        $this->addElement('submit', 'submit', array('label' => $adapter->translate('Create game')));
    }

}

