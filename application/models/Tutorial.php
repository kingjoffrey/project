<?php

class Application_Model_Tutorial extends Coret_Db_Table_Abstract
{
    protected $_name = 'tutorial';
    protected $_primary = 'tutorialId';
    protected $_playerId;

    public function __construct($playerId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_playerId = $playerId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function get()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId);

        return $this->selectRow($select);
    }

    public function getNumber()
    {
        $select = $this->_db->select('tutorialNumber')
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId);

        return $this->selectOne($select);
    }

    public function add()
    {
        $data = array(
            'playerId' => $this->_playerId
        );

        $this->insert($data);
    }

    public function updateStep($step, $tutorialNumber)
    {
        $data = array(
            'step' => $step
        );
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('tutorialNumber') . ' = ?', $tutorialNumber),
        );

        $this->update($data, $where);
    }

    public function updateNumber($oldTutorialNumber, $newTutorialNumber)
    {
        $data = array(
            'tutorialNumber' => $newTutorialNumber
        );
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('tutorialNumber') . ' = ?', $oldTutorialNumber),
        );

        $this->update($data, $where);
    }
}
