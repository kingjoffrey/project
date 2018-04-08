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

    public function up($heroId, $levelBonusId)
    {
        $data = array(
            'heroId' => $heroId,
            'levelbonusId' => $levelBonusId
        );

        return $this->insert($data);
    }

    public function getBonuses($heroId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'levelbonusId')
            ->where($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId);

        $bonus = array();

        foreach ($this->selectAll($select) as $row) {
            $bonus[] = $row['levelbonusId'];
        }
        return $bonus;
    }
}
