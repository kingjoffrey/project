<?php

class Application_Form_NumberOfPlayers extends Zend_Form
{

    public function init()
    {
        $mMapPlayers = new Application_Model_MapPlayers($this->_attribs['mapId']);
        $numberOfPlayers = $mMapPlayers->getNumberOfPlayersForNewGame();

        $f = new Coret_Form_Varchar(
            array(
                'name' => 'x',
                'label' => $this->getView()->translate('Number of players') . ':',
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

