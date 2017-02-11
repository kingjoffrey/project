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
            $map['date'] = Coret_View_Helper_Formatuj::date($map['date']);
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

    function create(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $view->formCreate = new Application_Form_Createmap ();
        $view->formCreate->setView($view);

        if (isset($dataIn['name']) && $view->formCreate->isValid($dataIn)) {
            $db = $handler->getDb();

            $mMap = new Application_Model_Map (0, $db);
            $mapId = $mMap->create($view->formCreate->getValues(), $user->parameters['playerId']);

            $mSide = new Application_Model_Side(0, $db);

            $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
            $mMapPlayers->create($mSide->getWithLimit($dataIn['maxPlayers']));

            $token = array(
                'type' => 'editor',
                'action' => 'generate',
                'mapId' => $mapId
            );
        } else {
            $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

            $token = array(
                'type' => 'editor',
                'action' => 'create',
                'data' => $view->render('editor/create.phtml')
            );
        }
        $handler->sendToUser($user, $token);
    }

    function mirror(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['id']) || empty($dataIn['id'])) {
            echo('mirror: brak mapId' . "\n");
            return;
        }

        $db = $handler->getDb();

        $mMap = new Application_Model_Map ($dataIn['id'], $db);
        $oldMap = $mMap->get();
        $mCastles = new Application_Model_MapCastles($dataIn['id'], $db);
        $oldCastles = $mCastles->getMapCastles();
        $mTowers = new Application_Model_MapTowers($dataIn['id'], $db);
        $oldTowers = $mTowers->getMapTowers();
        $mRuins = new Application_Model_MapRuins($dataIn['id'], $db);
        $oldRuins = $mRuins->getMapRuins();

        switch ($dataIn['mirror']) {
            case 0:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorTop();
                break;
            case 1:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorRight();
                break;
            case 2:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorBottom();
                break;
            default:
                $mapFields = new Application_Model_MapFields($oldMap['mapId'], $db);
                $fields = $mapFields->mirrorLeft();
                break;
        }

        if ($mapId = $mMap->create(array('maxPlayers' => $oldMap['maxPlayers'], 'name' => $oldMap['name'] . ' mirror'), $user->parameters['playerId'])) {

            $mSide = new Application_Model_Side(0, $db);

            $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
            $mMapPlayers->create($mSide->getWithLimit($oldMap['maxPlayers']));

            $mapFields = new Application_Model_MapFields($mapId, $db);
            foreach ($fields as $y => $row) {
                foreach ($row as $x => $type) {
                    $mapFields->add($x, $y, $type);
                }
            }

            $maxY = count($fields) / 2;

            $mCastles = new Application_Model_MapCastles($mapId, $db);
            foreach ($oldCastles as $castleId => $castle) {
                switch ($dataIn['mirror']) {
                    case 0:
                        $mCastles->add($castle['x'], $castle['y'] + $maxY, $castle);
                        $y = ($maxY - ($castle['y'] * 2 + 1)) + $castle['y'] - 1;
                        $mCastles->add($castle['x'], $y, $castle);
                        break;
                    case 1:
                        $mCastles->add($castle['x'], $castle['y'], $castle);
                        $x = count($fields[0]) - ($castle['x'] + 1) - 1;
                        $mCastles->add($x, $castle['y'], $castle);
                        break;
                    case 2:
                        $mCastles->add($castle['x'], $castle['y'], $castle);
                        $y = ($maxY - ($castle['y'] + 1)) + $maxY - 1;
                        $mCastles->add($castle['x'], $y, $castle);
                        break;
                    case 3:
                        break;
                }
            }

            $mTowers = new Application_Model_MapTowers($mapId, $db);
            foreach ($oldTowers as $towerId => $tower) {
                switch ($dataIn['mirror']) {
                    case 0:
                        $mTowers->add($tower['x'], $tower['y'] + $maxY);
                        $y = ($maxY - ($tower['y'] * 2 + 1)) + $tower['y'];
                        $mTowers->add($tower['x'], $y);
                        break;
                    case 1:
                        $mTowers->add($tower['x'], $tower['y']);
                        $x = count($fields[0]) - ($tower['x'] + 1);
                        $mTowers->add($x, $tower['y']);
                        break;
                    case 2:
                        $mTowers->add($tower['x'], $tower['y']);
                        $y = ($maxY - ($tower['y'] + 1)) + $maxY;
                        $mTowers->add($tower['x'], $y);
                        break;
                    case 3:
                        break;
                }
            }

            $mRuins = new Application_Model_MapRuins($mapId, $db);
            foreach ($oldRuins as $ruinId => $ruin) {
                switch ($dataIn['mirror']) {
                    case 0:
                        $mRuins->add($ruin['x'], $ruin['y'] + $maxY);
                        $y = ($maxY - ($ruin['y'] * 2 + 1)) + $ruin['y'];
                        $mRuins->add($ruin['x'], $y);
                        break;
                    case 1:
                        $mRuins->add($ruin['x'], $ruin['y']);
                        $x = count($fields[0]) - ($ruin['x'] + 1);
                        $mRuins->add($x, $ruin['y']);
                        break;
                    case 2:
                        $mRuins->add($ruin['x'], $ruin['y']);
                        $y = ($maxY - ($ruin['y'] + 1)) + $maxY;
                        $mRuins->add($ruin['x'], $y);
                        break;
                    case 3:
                        break;
                }
            }

            $token = array(
                'type' => 'editor',
                'action' => 'add',
                'map' => array(
                    'mapId' => $mapId,
                    'name' => $oldMap['name'] . ' mirror',
                    'maxPlayers' => $oldMap['maxPlayers'],
                    'date' => Coret_View_Helper_Formatuj::date(time())
                )
            );

            $handler->sendToUser($user, $token);
        }
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
}