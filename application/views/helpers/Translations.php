<?php

class Zend_View_Helper_Translations extends Zend_View_Helper_Abstract
{

    public function translations()
    {
        echo Zend_Json::encode(array(
            'castlesHeld' => $this->view->translate('Castles held'),
            'castlesConquered' => $this->view->translate('Castles conquered'),
            'castlesLost' => $this->view->translate('Castles lost'),
            'castlesRazed' => $this->view->translate('Castles razed'),
            'unitsCreated' => $this->view->translate('Units created'),
            'unitsKilled' => $this->view->translate('Units killed'),
            'unitsLost' => $this->view->translate('Units lost'),
            'heroesKilled' => $this->view->translate('Heroes killed'),
            'heroesLost' => $this->view->translate('Heroes lost'),
            'statistics' => $this->view->translate('Statistics'),
            'gameOver' => $this->view->translate('GAME OVER'),
            'thisIsTheEnd' => $this->view->translate('This is THE END'),
            'towers' => $this->view->translate('towers'),
            'tower' => $this->view->translate('Tower'),
            'gold' => $this->view->translate('gold'),
            'castles' => $this->view->translate('castles'),
            'upkeep' => $this->view->translate('Upkeep'),
            'goldPerTurn' => $this->view->translate('gold per turn'),
            'summation' => $this->view->translate('Summation'),
            'income' => $this->view->translate('Income'),
            'hireHero' => $this->view->translate('Hire hero'),
            'doYouWantToHireNewHeroFor1000Gold' => $this->view->translate('Do you want to hire new hero for 1000 gold?'),
            'resurrectHero' => $this->view->translate('Resurrect hero'),
            'doYouWantToResurrectHeroFor100Gold' => $this->view->translate('Do you want to resurrect hero for 100 gold?'),
            'battleConfiguration' => $this->view->translate('Battle configuration'),
            'changeBattleAttackSequenceByMovingUnits' => $this->view->translate('Change battle attack sequence by moving units'),
            'changeBattleDefenceSequenceByMovingUnits' => $this->view->translate('Change battle defence sequence by moving units'),
            'ok' => $this->view->translate('Ok'),
            'cancel' => $this->view->translate('Cancel'),
            'error' => $this->view->translate('Error'),
            'surrender' => $this->view->translate('Surrender'),
            'areYouSure' => $this->view->translate('Are you sure?'),
            'yourTurn' => $this->view->translate('Your turn'),
            'thisIsYourTurnNow' => $this->view->translate('This is your turn now'),
            'capitalCity' => $this->view->translate('capital city'),
            'ground' => $this->view->translate('ground'),
            'air' => $this->view->translate('air'),
            'water' => $this->view->translate('water'),
            'time' => $this->view->translate('Time'),
            'cost' => $this->view->translate('Cost'),
            'stopProduction' => $this->view->translate('Stop production'),
            'productionRelocation' => $this->view->translate('Production relocation'),
            'sorryServerIsDisconnected' => $this->view->translate('Sorry, server is disconnected'),
            'relocation' => $this->view->translate('Relocation'),
            'selectCastleToWhichYouWantToRelocateThisProduction' => $this->view->translate('Select castle to which you want to relocate this production'),
            'productionRelocated' => $this->view->translate('Production relocated'),
            'productionStopped' => $this->view->translate('Production stopped'),
            'productionSet' => $this->view->translate('Production set'),
            'production' => $this->view->translate('Production'),
            'relocatingTo' => $this->view->translate('Relocating to'),
            'relocatingFrom' => $this->view->translate('Relocating from'),
            'nextTurn' => $this->view->translate('Next turn'),
            'disbandArmy' => $this->view->translate('Disband army'),
            'castleDefense' => $this->view->translate('Castle defense'),
            'gold_turn' => $this->view->translate('gold/turn'),
            'movesLeft' => $this->view->translate('Moves left'),
            'status' => $this->view->translate('Status'),
            'split' => $this->view->translate('Split'),
            'attack' => $this->view->translate('Attack'),
            'defence' => $this->view->translate('Defence'),
            'human' => $this->view->translate('Human'),
            'select' => $this->view->translate('Select'),
            'deselect' => $this->view->translate('Deselect'),
            'computer' => $this->view->translate('Computer'),
            'startGame' => $this->view->translate('Start game'),
            'defencePoints' => $this->view->translate('Defence points'),
            'attackPoints' => $this->view->translate('Attack points'),
            'defaultMoves' => $this->view->translate('Default moves'),
            'currentMoves' => $this->view->translate('Current moves'),
            'movementCostThroughTheForest' => $this->view->translate('Movement cost through the forest'),
            'movementCostThroughTheHills' => $this->view->translate('Movement cost through the hills'),
            'movementCostThroughTheSwamp' => $this->view->translate('Movement cost through the swamp'),
            'destroyCastle' => $this->view->translate('Destroy castle'),
            'battleSequence' => $this->view->translate('Battle sequence'),
            'attackSequenceSuccessfullyUpdated' => $this->view->translate('Attack sequence successfully updated'),
            'defenceSequenceSuccessfullyUpdated' => $this->view->translate('Defence sequence successfully updated'),
            'ruins' => $this->view->translate('Ruins'),
            'ruin' => $this->view->translate('Ruin'),
            'youHaveFound' => $this->view->translate('You have found'),
            'ruinsAreEmpty' => $this->view->translate('Ruins are empty'),
            'death' => $this->view->translate('death'),
            'youHaveFoundNothing' => $this->view->translate('You have found nothing'),
            'alliesJoinedYourArmy' => $this->view->translate('allies joined your army'),
            'anAncientArtifact' => $this->view->translate('an ancient artifact'),
            'itIsNotYourTurn' => $this->view->translate('It is not your turn'),
            'noCastleToDestroy' => $this->view->translate('No castle to destroy'),
            'noCastleToBuildDefense' => $this->view->translate('No castle to build defense'),
            'currentDefense' => $this->view->translate('Current defense'),
            'newDefense' => $this->view->translate('New defense'),
            'doYouWantToBuildCastleDefense' => $this->view->translate('Do you want to build castle defense?'),
            'turn' => $this->view->translate('turn'),
            'moves' => $this->view->translate('Moves'),
            'battle' => $this->view->translate('Battle'),
            'thereIsNoFreeArmyToWithSpareMovePoints' => $this->view->translate('There is no free army with spare move points. Change turn!'),
            'nextArmy' => $this->view->translate('Next army'),
            'army' => $this->view->translate('Army'),
            'noMoreMoves' => $this->view->translate('No more moves'),
            'noUnitSelected' => $this->view->translate('No unit selected'),
            'availableUnits' => $this->view->translate('Available units'),
            'productionTime' => $this->view->translate('Production time'),
            'costOfLiving' => $this->view->translate('Cost of living'),
            'movementPoints' => $this->view->translate('Movement points'),
            'attackPoints' => $this->view->translate('Attack points'),
            'defencePoints' => $this->view->translate('Defence points'),
            'startProduction' => $this->view->translate('Start production'),
            'close' => $this->view->translate('Close'),
            'castle' => $this->view->translate('Castle'),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
            '' => $this->view->translate(''),
        ));
    }

}
