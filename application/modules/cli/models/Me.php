<?php

class Cli_Model_Me
{
    protected $_id;
    protected $_color;

    public function __construct($color, $playerId)
    {
        $this->_color = $color;
        $this->_id = $playerId;
    }

    public function toArray()
    {
        return array(
            'color' => $this->_color
        );
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getColor()
    {
        return $this->_color;
    }

    /**
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @return Cli_Model_Me
     */
    static public function getMe(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['me'];
    }
}