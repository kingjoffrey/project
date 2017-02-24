<?php

class Zend_View_Helper_Terrain extends Zend_View_Helper_Abstract
{

    public function terrain()
    {
        $mTerrain = new Application_Model_Terrain();
        $Terrain = new Cli_Model_TerrainTypes($mTerrain->getTerrain());

        $this->view->headScript()->appendScript('var terrain = ' . Zend_Json::encode($Terrain->toArray()));
    }
}
