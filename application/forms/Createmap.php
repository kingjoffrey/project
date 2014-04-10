<?php

class Application_Form_Createmap extends Zend_Form
{

    public function init()
    {
        $dimensions = array(33 => 33, 65 => 65, 129 => 129, 257 => 257);
        $numberOfPlayers = array(2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8);

        $this->setMethod('post');

        $f = new Coret_Form_Varchar(
            array(
                'name' => 'name',
                'label' => $this->getView()->translate('Map name'),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('Alnum'),
                )
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Select(
            array(
                'name' => 'mapWidth',
                'label' => $this->getView()->translate('Map width'),
                'required' => true,
                'opt' => $dimensions,
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Select(array(
            'name' => 'mapHeight',
            'label' => $this->getView()->translate('Map height'),
            'required' => true,
            'opt' => $dimensions,
        ));
        $this->addElements($f->getElements());

        $f = new Coret_Form_Select(
            array(
                'name' => 'maxPlayers',
                'label' => $this->getView()->translate('Number of players'),
                'required' => true,
                'opt' => $numberOfPlayers,
            )
        );
        $this->addElements($f->getElements());

        $this->addElement('submit', 'submit', array('label' => 'Create map'));
    }
}
