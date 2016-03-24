<?php

class zend_View_Helper_HelpMenu extends Zend_View_Helper_Abstract
{
    public function helpMenu()
    {
        $this->view->placeholder('helpMenu')
            ->setPrefix('<div id="helpMenu">')
            ->setPostfix('</div>');

        foreach (Admin_Model_Help::getActionArray() as $key => $val) {
            $this->view->placeholder('helpMenu')->append('<div id="' . $key . '">' . $this->view->translate($val) . '</div>');
        }
    }

}
