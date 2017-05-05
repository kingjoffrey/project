<?php

class Cli_Model_Help
{
    private $_array = array();

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $mHelp = new Application_Model_Help($db);
        $help = $mHelp->get();

        $menu = Admin_Model_Help::getMenuArray();
        foreach ($menu as $key => $val) {
            foreach ($help as $k => $row) {
                if ($key == $row['menu']) {
                    if (!isset($this->_array[$key])) {
                        $this->_array[$key] = array();
                    }
                    unset($row['menu']);
                    $this->_array[$key][] = $row;
                    unset($help[$k]);
                }
            }
            $this->_array['menu'][$key] = $adapter->translate($val);
        }

        $mUnit = new Application_Model_Unit($db);
        $this->_array['list'] = $mUnit->getUnits();
    }

    public function toArray()
    {
        return $this->_array;
    }
}