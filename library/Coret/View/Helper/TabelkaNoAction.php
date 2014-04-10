<?php

class Coret_View_Helper_TabelkaNoAction extends Coret_View_Helper_Tabelka
{

    public function tabelkaNoAction(array $kolumny, $kontroler, $primary)
    {
        return $this->create($kolumny, $kontroler, $primary);
    }

    protected function createButtons($kontroler, $id, $params = null)
    {
        return '<td></td>';
    }

}