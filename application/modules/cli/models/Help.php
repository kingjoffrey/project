<?php

class Cli_Model_Help
{
    private $_help = array();

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
                    if (!isset($this->_help[$key])) {
                        $this->_help[$key] = array();
                    }
                    unset($row['menu']);
                    $this->_help[$key][] = $row;
                    unset($help[$k]);
                }
            }
            $this->_help['menu'][$key] = $adapter->translate($val);
        }

        $mUnit = new Application_Model_Unit($db);
        $this->_help['list'] = $mUnit->getUnits();
    }

    public function toArray()
    {
        return $this->_help;
    }
}