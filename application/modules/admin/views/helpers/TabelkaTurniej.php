<?php

class Admin_View_Helper_TabelkaTurniej extends Admin_View_Helper_Tabelka
{

    public function tabelkaTurniej(array $kolumny, $kontroler, $primary)
    {
        return $this->create($kolumny, $kontroler, $primary);
    }

    protected function createButtons($kontroler, $id, $params = null)
    {
        return '<td>
<a onclick="document.location = \'/admin/' . $kontroler . '/edit/id/' . $id . '\'">Zmień dane</a>
</td><td>
<a onclick="document.location = \'/admin/' . $kontroler . '/stage/id/' . $id . '\'">Zmień etap</a>
</td>';
    }

}