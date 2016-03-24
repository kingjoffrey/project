<?php

class zend_View_Helper_HelpMenu extends Zend_View_Helper_Abstract
{
    public function helpMenu()
    {
        $this->view->placeholder('helpMenu')
            ->setPrefix('<div id="helpMenu">')
            ->setPostfix('</div>');

        $menu = array(
            'units' => $this->view->translate('Units'),
            'hero' => $this->view->translate('Hero'),
            'castle' => $this->view->translate('Castle'),
            'tower' => $this->view->translate('Tower'),
            'ruin' => $this->view->translate('Ruin'),
            'terrain' => $this->view->translate('Terrain'),
            'army' => $this->view->translate('Army'),
            'game' => $this->view->translate('Game'),
            '' => $this->view->translate(''),
        );

        foreach ($menu as $key => $val) {
            $this->view->placeholder('helpMenu')->append('<div id="' . $key . '">' . $val . '</div>');
        }
    }

}
