<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class EditorController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $db = $handler->getDb();
        $mMap = new Application_Model_Map (0, $db);

        $list = array();

        foreach ($mMap->getPlayerMapList($user->parameters['playerId']) as $map) {
            $map['date'] = Coret_View_Helper_Formatuj::date($map['date'], 'Y-m-d H:i:s');
            $list[] = $map;
        }

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'editor',
            'action' => 'index',
            'data' => $view->render('editor/index.phtml'),
            'list' => $list
        );

        $handler->sendToUser($user, $token);
    }

    function create(WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $view = new Zend_View();
        $view->formCreate = new Application_Form_Createmap();
        $view->formCreate->setView($view);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'editor',
            'action' => 'create',
            'data' => $view->render('editor/create.phtml')
        );

        $handler->sendToUser($user, $token);
    }

    function delete(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['id'])) {
            return;
        }

        $db = $handler->getDb();

        $mMap = new Application_Model_Map($dataIn['id'], $db);

        if ($mMap->deleteNotPublished($user->parameters['playerId'])) {
            $token = array(
                'type' => 'editor',
                'action' => 'delete',
                'id' => $dataIn['id']
            );

            $handler->sendToUser($user, $token);
        }
    }

    function save(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['id'])) {
            return;
        }

        $db = $handler->getDb();

        $mMap = new Application_Model_Map($dataIn['id'], $db);

        if ($mMap->changeNameAndMaxPlayers($dataIn, $user->parameters['playerId'])) {
            $token = array(
                'type' => 'editor',
                'action' => 'save',
                'id' => $dataIn['id']
            );

            $handler->sendToUser($user, $token);
        }
    }
}