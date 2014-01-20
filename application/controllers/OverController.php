<?php

class OverController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $gameId = $this->_request->getParam('id');
        if (empty($gameId)) {
            throw new Exception('Brak game ID!');
        }

        $mGameScore = new Application_Model_GameScore($gameId);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
        $this->view->score = $mGameScore->get();
        $this->view->players = $mPlayersInGame->getAllColors();
//        var_dump($this->view->players);exit;
//        $this->_namespace->player['playerId'];
    }

}