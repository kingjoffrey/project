<?php

class Cli_Model_Editor
{
    private $_id;
    private $_fields;


    public function __construct($mapId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $mapId;

        $mMapFields = new Application_Model_MapFields($this->_id, $db);
        $this->_fields = new Cli_Model_Fields($mMapFields->getMapFields());
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'fields' => $this->_fields->toArray()
        );
    }

    /**
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @return Cli_Model_Editor
     */
    static public function getEditor(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['editor'];
    }
}