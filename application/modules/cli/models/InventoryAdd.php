<?php

class Cli_Model_InventoryAdd
{

    public function __construct($heroId, $artifactId, $user, $db, $handler)
    {
        if ($heroId == null) {
            $handler->sendError($user, 'Brak "heroId"!');
            return;
        }

        $mHero = new Application_Model_Hero($user->parameters['playerId'], $db);
        if (!$mHero->isMyHero($heroId)) {
            $handler->sendError($user, 'To nie jest Twój hero.');
            return;
        }

        $mChest = new Application_Model_Chest($user->parameters['playerId'], $db);
        if (!$mChest->artifactExists($artifactId)) {
            $handler->sendError($user, 'Tego artefaktu nie ma w skrzyni.');
            return;
        }

        $mInventory = new Application_Model_Inventory($heroId, $user->parameters['gameId'], $db);
        if ($mInventory->itemExists($artifactId)) {
            $handler->sendError($user, 'Ten artefakt już jest w Twoim ekwipunku.');
            return;
        }

        $mInventory->addArtifact($artifactId);

        $token = array(
            'type' => 'inventoryAdd',
            'heroId' => $heroId,
            'artifactId' => $artifactId
        );

        $handler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}