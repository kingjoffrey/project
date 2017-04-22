<?php

class Application_Model_PayPal extends Coret_Db_Table_Abstract
{
    protected $_name = 'paypal';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function addPayment($paymentId, $playerId)
    {
        $data = array(
            'paymentId' => $paymentId,
            'playerId' => $playerId
        );

        return $this->_db->insert($this->_name, $data);
    }

    public function checkPayment($paymentId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'paymentId')
            ->where($this->_db->quoteIdentifier('paymentId') . ' = ?', $paymentId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        return $this->selectOne($select);
    }
}

