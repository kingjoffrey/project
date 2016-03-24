<?php

class zend_View_Helper_HelpMenu extends Zend_View_Helper_Abstract
{
    public function helpMenu()
    {
        $this->view->placeholder('helpMenu')
            ->setPrefix('<div id="helpMenu">')
            ->setPostfix('</div>');

        $menu = array(
            'game' => $this->view->translate('Game'),
            'castle' => $this->view->translate('Castle'),
            'army' => $this->view->translate('Army'),
            'units' => $this->view->translate('Units'),
            'hero' => $this->view->translate('Hero'),
            'ruin' => $this->view->translate('Ruin'),
            'tower' => $this->view->translate('Tower'),
            'terrain' => $this->view->translate('Terrain'),
        );

        foreach ($menu as $key => $val) {
            $this->view->placeholder('helpMenu')->append('<div id="' . $key . '">' . $val . '</div>');
        }
    }

}
