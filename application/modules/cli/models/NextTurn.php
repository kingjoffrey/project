<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_NextTurn
{

    public function __construct(WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $players = $game->getPlayers();
        $db = $handler->getDb();
        while (true) {
            $nextPlayerId = $this->getExpectedNextTurnPlayer($game, $handler);
            if (!$nextPlayerId) {
                $mSR = new Cli_Model_SaveResults($game, $db);
                $mSR->sendToken($handler);
                return;
            }
            $nextPlayerColor = $game->getPlayerColor($nextPlayerId);

            $player = $players->getPlayer($nextPlayerColor);
            if ($player->armiesOrCastlesExists()) {

                $turnNumber = $game->getTurnNumber();
                $turnsLimit = $game->getTurnsLimit();

                if ($turnsLimit && $turnNumber > $turnsLimit) {
                    $mSR = new Cli_Model_SaveResults($game, $db);
                    $mSR->sendToken($handler);
                    return;
                }

                $mTurnHistory = new Application_Model_TurnHistory($game->getId(), $db);
                $mTurnHistory->add($nextPlayerId, $turnNumber);

                $token = array(
                    'type' => 'nextTurn',
                    'nr' => $turnNumber,
                    'color' => $nextPlayerColor
                );
                $handler->sendToChannel($token);
                return;
            } else {
//                $players->getPlayer($nextPlayerColor)->setLost($game->getId(), $db);

                echo 'Jeśli to widzisz to sprawdź to koniecznie, teoretycznie nie powinno to działać i jest niepotrzebne. (20170423)' . "\n";
                echo 'Jeśli to widzisz to sprawdź to koniecznie, teoretycznie nie powinno to działać i jest niepotrzebne. (20170423)' . "\n";
                echo 'Jeśli to widzisz to sprawdź to koniecznie, teoretycznie nie powinno to działać i jest niepotrzebne. (20170423)' . "\n";

//                $token = array(
//                    'type' => 'dead',
//                    'color' => $nextPlayerColor
//                );
//                $handler->sendToChannel($token);
            }
        }
    }

    private function getExpectedNextTurnPlayer(Cli_Model_Game $game, $handler)
    {
        $currentPlayerColor = $game->getPlayerColor($game->getTurnPlayerId());
        $find = false;
        $playersInGameColors = $game->getPlayersColors();

        reset($playersInGameColors);
        $firstColor = current($playersInGameColors);

        /* szukam następnego koloru w dostępnych kolorach */
        foreach ($playersInGameColors as $color) {
            /* znajduję kolor gracza, który ma aktualnie turę i przewijam na następny */
            if ($currentPlayerColor == $color) {
                $find = true;
                continue;
            }

            /* to jest przewinięty kolor gracza */
            if ($find && $game->getPlayers()->getPlayer($color)->armiesOrCastlesExists()) {
                $nextPlayerColor = $color;
                break;
            }
        }

        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
        if (!isset($nextPlayerColor)) {
            $nextPlayerColor = $firstColor;
        }

        /* jeśli następny kolor to ten sam, który rozpoczął zmianę tury to nie ma więcej żywych graczy - zakończ grę */
        if ($nextPlayerColor == $currentPlayerColor) {
            return;
        }

        /* jeżeli następny gracz to pierwszy gracz to wtedy nowa tura */
        if ($nextPlayerColor == $firstColor) {
            if ($game->getPlayers()->getPlayer($nextPlayerColor)->armiesOrCastlesExists()) {
                $game->turnNumberIncrement();
                $token = array(
                    'type' => 'neutral',
                    'armies' => $game->getPlayers()->getPlayer('neutral')->getArmies()->toArray()
                );
                $handler->sendToChannel($token);
            } else {
                return;
            }
        }
        $turnPlayerId = $game->getPlayers()->getPlayer($nextPlayerColor)->getId();
        $game->setTurnPlayerId($turnPlayerId);

        $mGame = new Application_Model_Game($game->getId(), $handler->getDb());
        $mGame->updateTurn($turnPlayerId, $game->getTurnNumber());

        return $turnPlayerId;
    }
}
