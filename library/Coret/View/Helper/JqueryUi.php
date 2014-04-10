<?php

class Coret_View_Helper_JqueryUi extends Zend_View_Helper_Abstract
{

    public function jqueryUi()
    {
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/jquery-ui.min.js'));
        $this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/ui-lightness/jquery-ui.css'));
        $this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/ui-lightness/jquery.ui.datepicker.min.css'));
    }

}