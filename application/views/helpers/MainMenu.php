<?php

class zend_View_Helper_MainMenu extends Zend_View_Helper_Abstract
{
    public function mainMenu()
    {
        $lang = Zend_Registry::get('lang');

        $controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

        $this->view->placeholder('mainMenu')
            ->setPrefix('<div id="menu">')
            ->setPostfix('</div>');

        $menu = array(
            'play' => $this->view->translate('Play'),
            'load' => $this->view->translate('Load game'),
            'halloffame' => $this->view->translate('Hall of Fame'),
//            'hero' => $this->view->translate('Hero'),
            'players' => $this->view->translate('Players'),
            'profile' => $this->view->translate('Profile'),
            'help' => $this->view->translate('Help'),
//            'stats' => $this->view->translate('Stats'),
            'editor' => $this->view->translate('Map editor'),
//            'market' => $this->view->translate('Market'),
        );

        foreach ($menu as $key => $val) {

            if ($key == $controllerName) {
                $active = ' id="active"';
            } else {
                $active = '';
            }

            $this->view->placeholder('mainMenu')->append('<a' . $active . ' href="/' . $lang . '/' . $key . '" class="button">' . $val . '</a>');
        }
    }

}
