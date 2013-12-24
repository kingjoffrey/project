<?php

class Application_Form_Creategame extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $mMap = new Application_Model_Map();

        $f = new Coret_Form_Select(array('name' => 'mapId', 'label' => $this->getView()->translate('Select map'), 'opt' => $mMap->getAllMapsList()));
        $this->addElements($f->getElements());

        $f = new Application_Form_NumberOfPlayers();
        $this->addElements($f->getElements());

        $timeLimits = Application_Model_Limit::timeLimits();

        $f = new Coret_Form_Select(array('name' => 'timeLimit', 'label' => $this->getView()->translate('Select time limit'), 'opt' => $timeLimits));
        $this->addElements($f->getElements());

        $f = new Coret_Form_Number(
            array(
                'name' => 'turnsLimit',
                'label' => $this->getView()->translate('Turns limit'),
                'value' => 0
            )
        );
        $this->addElements($f->getElements());

        $turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $f = new Coret_Form_Select(array('name' => 'turnTimeLimit', 'label' => $this->getView()->translate('Select time limit per turn'), 'opt' => $turnTimeLimit));
        $this->addElements($f->getElements());

        $this->addElement('submit', 'submit', array('label' => $this->getView()->translate('Create game')));
    }

}

