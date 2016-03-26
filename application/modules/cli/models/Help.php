<?php

class Cli_Model_Help
{
    private $_help = array();

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mHelp = new Application_Model_Help($db);
        $help = $mHelp->get();

        $menu = Admin_Model_Help::getMenuArray();
        foreach (array_keys($menu) as $key) {
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
        }
    }

    public function toArray()
    {
        return $this->_help;
    }
}