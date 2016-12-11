<?php

class TutorialController extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';

    public function indexAction()
    {
        $this->view->gameId = $this->_request->getParam('id');
        if (empty($this->view->gameId)) {
            throw new Exception('Brak "gameId"!');
        }

        $version = Zend_Registry::get('config')->version;

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/kinetic-v4.7.4.min.js');
        $this->view->headScript()->appendFile('/js/date.js');

        $this->_helper->layout->setLayout('game');

        $this->view->sound();
        $this->view->models();
        $this->view->translations();
        $this->view->Websocket($this->_auth->getIdentity());
        $this->view->Friends();

        $this->view->headScript()->appendFile('/js/jquery-ui-1.10.3.custom.js');
        $this->view->headScript()->appendFile('/js/jquery.mousewheel.min.js');
        $this->view->headScript()->appendFile('/js/Tween.js');
        $this->view->headScript()->appendFile('/js/three/three.js');
        $this->view->headScript()->appendFile('/js/three/Detector.js');

        $this->view->headScript()->appendFile('/js/common/init.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/battleModels.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/battleScene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/castle.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/castles.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/renderers.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/unitScene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ground.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/game.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameRenderer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameScene.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/gameModels.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/models.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/field.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/fields.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/players.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruin.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/ruins.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/tower.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/towers.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/units.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/unitModels.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/unitRenderer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/execute.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/websocketSend.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/websocketMessage.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/common/me.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/picker.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/unit.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/terrain.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/castle.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/players.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/player.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/army.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/message.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/astar.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/chat.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/gui.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/move.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/minimap.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/timer.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/turn.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/sound.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/game/castleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/configurationWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/splitWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/statusWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/battleWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/treasuryWindow.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/game/statisticsWindow.js?v=' . $version);

        $this->view->headScript()->appendFile('/js/tutorial/init.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/tutorial/tutorial.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/tutorial/websocket.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/tutorial/websocketMessage.js?v=' . $version);

        $this->view->headLink()->appendStylesheet('/css/game.css?v=' . $version);
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . $version);
    }

    public function initAction()
    {
        $this->_helper->layout->setLayout('empty');
        $playerId = $this->_auth->getIdentity()->playerId;
        $mGame = new Application_Model_Game();
        $gameId = $mGame->getMyTutorial($playerId);
        if (!$gameId) {
            $mTutorial = new Application_Model_Tutorial($playerId);
            $number = $mTutorial->getNumber();
            switch ($number) {
                case 0:
                    $mapId = 296;
                    break;
                case 1:
                    $mapId = 295;
                    break;
                case 2:
                    $mapId = 298;
                    break;
                default:
                    $mapId = 296;
            }
            $gameId = $mGame->createGame(array(
                'numberOfPlayers' => 2,
                'gameMasterId' => $playerId,
                'mapId' => $mapId,
                'turnsLimit' => 0,
                'turnTimeLimit' => 0,
                'timeLimit' => 0,
            ), $playerId);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
            $mMapPlayers = new Application_Model_MapPlayers($mapId);
            $mMapCastles = new Application_Model_MapCastles($mapId);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId);
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId);
            $first = true;
            $startPositions = $mMapCastles->getDefaultStartPositions();
            foreach ($mMapPlayers->getAll() as $mapPlayerId => $mapPlayer) {
                if (!$playerId) {
                    $playerId = $mPlayersInGame->getComputerPlayerId();
                    if (!$playerId) {
                        $modelPlayer = new Application_Model_Player();
                        $playerId = $modelPlayer->createComputerPlayer();
                        $modelHero = new Application_Model_Hero($playerId);
                        $modelHero->createHero();
                    }
                }

                $mPlayersInGame->joinGame($playerId, $mapPlayerId);
                $mPlayersInGame->setTeam($playerId, $mapPlayerId);

                if ($first) {
                    $mTurn = new Application_Model_TurnHistory($gameId);
                    $mTurn->add($playerId, 1);
                    $mGame->startGame($playerId);
                    $first = false;
                }

                $mHero = new Application_Model_Hero($playerId);
                $playerHeroes = $mHero->getHeroes();
                if (empty($playerHeroes)) {
                    $mHero->createHero();
                    $playerHeroes = $mHero->getHeroes($playerId);
                }
                $mArmy = new Application_Model_Army($gameId);
                $armyId = $mArmy->createArmy($startPositions[$mapPlayer['mapPlayerId']], $playerId);
                $mHeroesInGame->add($armyId, $playerHeroes[0]['heroId']);
                $mCastlesInGame->addCastle($startPositions[$mapPlayer['mapPlayerId']]['mapCastleId'], $playerId);
                $playerId = 0;
            }
        }
        $this->redirect($this->view->url(array('action' => null, 'id' => $gameId)));
    }
}

