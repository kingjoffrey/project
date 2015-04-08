<?php

class Cli_Model_NextTurn
{

    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $game = Cli_Model_Game::getGame($user);
        $gameId = $game->getId();
        $players = $game->getPlayers();

        while (true) {
            $nextPlayerId = $this->getExpectedNextTurnPlayer($game, $db, $gameHandler);
            $nextPlayerColor = $game->getPlayerColor($nextPlayerId);

            $player = $players->getPlayer($nextPlayerColor);
            if ($player->armiesOrCastlesExists()) {

                $turnNumber = $game->getTurnNumber();
                $turnsLimit = $game->getTurnsLimit();

                if ($turnsLimit && $turnNumber > $turnsLimit) {
                    new Cli_Model_SaveResults($gameId, $db, $gameHandler);
                    return;
                }

                $mTurnHistory = new Application_Model_TurnHistory($gameId, $db);
                $mTurnHistory->add($nextPlayerId, $turnNumber);

                $token = array(
                    'type' => 'nextTurn',
                    'nr' => $turnNumber,
                    'color' => $nextPlayerColor
                );
                $gameHandler->sendToChannel($game, $token);
                return;
            } else {
                $players->getPlayer($nextPlayerColor)->setLost($gameId, $db);
                $token = array(
                    'type' => 'dead',
                    'color' => $nextPlayerColor
                );
                $gameHandler->sendToChannel($game, $token);
            }
        }
    }

    private function getExpectedNextTurnPlayer(Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $playerColor = $game->getPlayerColor($game->getTurnPlayerId());
        $find = false;
        $playersInGameColors = $game->getPlayersInGameColors();


        reset($playersInGameColors);
        $firstColor = current($playersInGameColors);

        /* szukam następnego koloru w dostępnych kolorach */
        foreach ($playersInGameColors as $color) {
            /* znajduję kolor gracza, który ma aktualnie turę i przewijam na następny */
            if ($playerColor == $color) {
                $find = true;
                continue;
            }

            /* to jest przewinięty kolor gracza */
            if ($find) {
                $nextPlayerColor = $color;
                break;
            }
        }

        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
        if (!isset($nextPlayerColor)) {
            $nextPlayerColor = $firstColor;
        }

        /* jeżeli następny gracz to pierwszy gracz to wtedy nowa tura */
        if ($nextPlayerColor == $firstColor) {
            $game->turnNumberIncrement();
            $token = array(
                'type' => 'neutral',
                'armies' => $game->getPlayers()->getPlayer('neutral')->getArmies()->toArray()
            );
            $gameHandler->sendToChannel($game, $token);
        }
        $turnPlayerId = $game->getPlayers()->getPlayer($nextPlayerColor)->getId();
        $game->setTurnPlayerId($turnPlayerId);

        $mGame = new Application_Model_Game($game->getId(), $db);
        $mGame->updateTurn($turnPlayerId, $game->getTurnNumber());

        return $turnPlayerId;
    }
}
