<?php

class Application_Model_Artifact extends Coret_Db_Table_Abstract
{
    protected $_name = 'artifact';
    protected $_primary = 'artifactId';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getArtifacts()
    {
        $select = $this->_db->select()
            ->from($this->_name);

        try {
            $result = $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }

        $artifacts = array();

        foreach ($result as $row) {
            $artifacts[$row[$this->_primary]] = $row;
        }

        return $artifacts;
    }

}