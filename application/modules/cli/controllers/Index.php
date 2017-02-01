<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class IndexController
{
    private $index = null;

    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        if (empty($this->index)) {
            $view = new Zend_View();
            $view->addScriptPath(APPLICATION_PATH . '/views/scripts');
            $this->index = $view->render('index/index.phtml');
        }

        $token = array(
            'type' => 'index',
            'action' => 'index',
            'data' => $this->index
        );
        $handler->sendToUser($user, $token);
    }
}