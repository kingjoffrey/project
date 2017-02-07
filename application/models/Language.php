<?php

class Application_Model_Language extends Coret_Db_Table_Abstract
{

    protected $_name = 'language';
    protected $_primary = 'languageId';
    protected $_sequence = 'language_languageId_seq';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getCountryCodeByLanguageId($id)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'countryCode')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $id);

        return $this->_db->fetchOne($select);
    }
}
