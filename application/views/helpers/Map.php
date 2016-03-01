<?php

class Zend_View_Helper_Map extends Zend_View_Helper_Abstract
{

    public function map($mapId)
    {
        $this->view->placeholder('map')->append('<img id="mapImage" src="/img/maps/' . $mapId . '.png"/>');
    }
}