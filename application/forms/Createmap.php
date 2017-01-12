<?php

class Application_Form_Createmap extends Zend_Form
{

    public function init()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

//        $dimensions = array(33 => 33, 65 => 65, 129 => 129, 257 => 257);
        $numberOfPlayers = array(2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8);

        $f = new Coret_Form_Varchar(
            array(
                'name' => 'name',
                'label' => $adapter->translate('Map name'),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('Alnum'),
                )
            )
        );
        $this->addElements($f->getElements());

//        $f = new Coret_Form_Select(array(
//            'name' => 'mapSize',
//            'label' => $adapter->translate('Map size'),
//            'required' => true,
//            'opt' => $dimensions,
//        ));
//        $this->addElements($f->getElements());
//
        $f = new Coret_Form_Select(
            array(
                'name' => 'maxPlayers',
                'label' => $adapter->translate('Number of players'),
                'required' => true,
                'opt' => $numberOfPlayers,
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('label' => $adapter->translate('Create map')));
        $this->addElements($f->getElements());
    }
}
