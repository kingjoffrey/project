<?php

class Application_Form_NumberOfPlayers extends Zend_Form
{

    public function init()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $mMapPlayers = new Application_Model_MapPlayers($this->_attribs['mapId'],$this->_attribs['db']);
        $numberOfPlayers = $mMapPlayers->getNumberOfPlayersForNewGame();

        $f = new Coret_Form_Varchar(
            array(
                'name' => 'x',
                'label' => $adapter->translate('Number of players') . ':',
                'value' => $numberOfPlayers,
                'attr' => array('disabled' => 'disabled')
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Hidden(
            array(
                'name' => 'numberOfPlayers',
                'value' => $numberOfPlayers,
                'validators' => array(array('Alnum'), array('identical', false, array(array('token' => $numberOfPlayers, 'strict' => FALSE)))),
            )
        );
        $this->addElements($f->getElements());
    }

}

