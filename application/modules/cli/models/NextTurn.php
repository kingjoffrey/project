<?php

class Cli_Model_NextTurn
{

    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $players = $game->getPlayers();
        $db = $handler->getDb();

        while (true) {
            $nextPlayerId = $this->getExpectedNextTurnPlayer($game, $handler);
            $nextPlayerColor = $game->getPlayerColor($nextPlayerId);

            $player = $players->getPlayer($nextPlayerColor);
            if ($player->armiesOrCastlesExists()) {

                $turnNumber = $game->getTurnNumber();
                $turnsLimit = $game->getTurnsLimit();

                if ($turnsLimit && $turnNumber > $turnsLimit) {
                    new Cli_Model_SaveResults($game, $handler);
                    return;
                }

                $mTurnHistory = new Application_Model_TurnHistory($game->getId(), $db);
                $mTurnHistory->add($nextPlayerId, $turnNumber);

                $token = array(
                    'type' => 'nextTurn',
                    'nr' => $turnNumber,
                    'color' => $nextPlayerColor
                );
                $handler->sendToChannel($game, $token);
                return;
            } else {
                $players->getPlayer($nextPlayerColor)->setLost($game->getId(), $db);
                $token = array(
                    'type' => 'dead',
                    'color' => $nextPlayerColor
                );
                $handler->sendToChannel($game, $token);
            }
        }
    }

    private function getExpectedNextTurnPlayer(Cli_Model_Game $game, $handler)
    {
        $playerColor = $game->getPlayerColor($game->getTurnPlayerId());
        $find = false;
        $playersInGameColors = $game->getPlayersColors();


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
            $handler->sendToChannel($game, $token);
        }
        $turnPlayerId = $game->getPlayers()->getPlayer($nextPlayerColor)->getId();
        $game->setTurnPlayerId($turnPlayerId);

        $mGame = new Application_Model_Game($game->getId(), $handler->getDb());
        $mGame->updateTurn($turnPlayerId, $game->getTurnNumber());

        return $turnPlayerId;
    }
}
