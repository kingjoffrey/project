<?php

class Application_Form_Team extends Zend_Form
{

    public function init()
    {
        if (isset($this->_attribs['db'])) {
            $db = $this->_attribs['db'];
        } else {
            $db = null;
        }

        $mMapPlayers = new Application_Model_MapPlayers($this->_attribs['mapId'], $db);

        $f = new Coret_Form_Select(array('name' => 'mapPlayerId', 'label' => null, 'opt' => $mMapPlayers->getLongNames()));

        $this->addElements($f->getElements());
    }

}

