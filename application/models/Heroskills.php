<?php

class Application_Model_Heroskills extends Coret_Db_Table_Abstract
{
    protected $_name = 'heroskills';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function up($heroId, $level, $levelbonusId)
    {
        $data = array(
            'heroId' => $heroId,
            'level' => $level,
            'levelbonusId' => $levelbonusId
        );

        return $this->insert($data);
    }

    public function getLevel($heroId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'max(level)')
            ->where($heroId . ' = ?', $heroId);

        return $this->selectOne($select);
    }

    public function getBonuses($heroId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('bId' => 'levelbonusId'))
            ->where($heroId . ' = ?', $heroId);

        return $this->selectAll($select);
    }
}
