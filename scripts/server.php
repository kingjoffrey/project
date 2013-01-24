<?php

date_default_timezone_set('Europe/Warsaw');
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));
set_include_path('../library');
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Custom_');

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'production');

// initialize Zend_Application
$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/configs/application.ini'
);

$config = new Zend_Config($application->getBootstrap()->getOptions());
Zend_Registry::set('config', $config);

declare(ticks = 1);

interface IWebSocketServerObserver {

    public function onConnect(IWebSocketConnection $user);

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg);

    public function onDisconnect(IWebSocketConnection $user);

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg);
}

/**
 * This demo resource handler will respond to all messages sent to /echo/ on the socketserver below
 *
 * All this handler does is echoing the responds to the user
 * @author Chris
 *
 */
class WofHandler extends WebSocket_UriHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $dataIn = Zend_Json::decode($msg->getData());
        print_r('ZAPYTANIE ');
        print_r($dataIn);

        switch ($dataIn['type'])
        {
            case 'move':
                $this->move($dataIn);
                break;

            case 'chat':
                $this->chat($dataIn);
                break;

            case 'army':
                $parentArmyId = $dataIn['data']['armyId'];
                if (empty($parentArmyId)) {
                    echo('Brak "armyId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $army = Application_Model_Database::getArmyById($dataIn['gameId'], $parentArmyId, $db);
                $army['color'] = Application_Model_Database::getPlayerColor($dataIn['gameId'], $army['playerId'], $db);
                $army['center'] = $dataIn['data']['center'];
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => $army,
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIdsExceptMine($dataIn['gameId'], $dataIn['playerId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'armies':
                $color = $dataIn['data']['color'];
                if (empty($color)) {
                    echo('Brak "color"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $playerId = Application_Model_Database::getPlayerIdByColor($dataIn['gameId'], $color, $db);
                if (empty($playerId)) {
                    echo('Brak $playerId!');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => Application_Model_Database::getPlayerArmies($dataIn['gameId'], $playerId),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIdsExceptMine($dataIn['gameId'], $dataIn['playerId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'splitArmy':
                $parentArmyId = $dataIn['data']['armyId'];
                $s = $dataIn['data']['s'];
                $h = $dataIn['data']['h'];
                if (empty($parentArmyId) || (empty($h) && empty($s))) {
                    echo('Brak "armyId", "s" lub "h"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $childArmyId = Application_Model_Database::splitArmy($dataIn['gameId'], $h, $s, $parentArmyId, $dataIn['playerId'], $db);
                if (empty($childArmyId)) {
                    echo('Brak "childArmyId"');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'parentArmyId' => $parentArmyId,
                        'childArmy' => Application_Model_Database::getArmyById($dataIn['gameId'], $childArmyId, $db),
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'joinArmy':
                $armyId1 = $dataIn['data']['armyId1'];
                $armyId2 = $dataIn['data']['armyId2'];
                if (empty($armyId1) || empty($armyId2)) {
                    echo('Brak "armyId1" i "armyId2"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $position1 = Application_Model_Database::getArmyPositionByArmyId($dataIn['gameId'], $armyId1, $dataIn['playerId'], $db);
                $position2 = Application_Model_Database::getArmyPositionByArmyId($dataIn['gameId'], $armyId2, $dataIn['playerId'], $db);
                if (empty($position1['x']) || empty($position1['y']) || ($position1['x'] != $position2['x']) || ($position1['y'] != $position2['y'])) {
                    echo('Armie nie są na tej samej pozycji!');
                    return;
                }
                $armyId = Application_Model_Database::joinArmiesAtPosition($dataIn['gameId'], $position1, $dataIn['playerId'], $db);
                if (empty($armyId)) {
                    echo('Brak "armyId"!');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Application_Model_Database::getArmyById($dataIn['gameId'], $armyId, $db),
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'disbandArmy':
                $armyId = $dataIn['data']['armyId'];
                if (!empty($armyId)) {
                    echo('Brak "armyId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $destroyArmyResponse = Application_Model_Database::destroyArmy($dataIn['gameId'], $armyId, $dataIn['playerId'], $db);
                if (!$destroyArmyResponse) {
                    echo('Nie mogę usunąć armii!');
                    return;
                }

                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'armyId' => $armyId,
                        'x' => $dataIn['data']['x'],
                        'y' => $dataIn['data']['y']
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'heroResurrection':
                $castleId = $dataIn['data']['castleId'];
                if ($castleId != null) {
                    echo('Brak "castleId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                if (!Application_Model_Database::isPlayerCastle($dataIn['gameId'], $castleId, $dataIn['playerId'], $db)) {
                    echo('To nie jest Twój zamek! ' . $castleId);
                    return;
                }
                if (!Application_Model_Database::isHeroInGame($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    Application_Model_Database::connectHero($dataIn['gameId'], $dataIn['playerId'], $db);
                }
                $heroId = Application_Model_Database::getDeadHeroId($dataIn['gameId'], $dataIn['playerId'], $db);
                if (!$heroId) {
                    echo('Twój heros żyje! ' . $heroId);
                    return;
                }
                $gold = Application_Model_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db);
                if ($gold < 100) {
                    echo('Za mało złota!');
                    return;
                }
                $position = Application_Model_Board::getCastlePosition($castleId);
                $armyId = Application_Model_Database::heroResurection($dataIn['gameId'], $heroId, $position, $dataIn['playerId'], $db);
                $gold -= 100;
                Application_Model_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $gold, $db);

                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Application_Model_Database::getArmyById($dataIn['gameId'], $armyId, $db),
                        'gold' => $gold
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'tower':

                break;

            case 'ruin':
                $parentArmyId = $dataIn['data']['armyId'];
                if (!Zend_Validate::is($parentArmyId, 'Digits')) {
                    echo('Brak "armyId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $heroId = Application_Model_Database::getHeroIdByArmyIdPlayerId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);
                if (empty($heroId)) {
                    echo('Brak heroId. Tylko Hero może przeszukiwać ruiny!');
                    return;
                }
                $position = Application_Model_Database::getArmyPositionByArmyId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);
                $ruinId = Application_Model_Board::confirmRuinPosition($position);
                if (!Zend_Validate::is($ruinId, 'Digits')) {
                    echo('Brak ruinId na pozycji');
                    return;
                }
                if (Application_Model_Database::ruinExists($dataIn['gameId'], $ruinId, $db)) {
                    echo('Ruiny są już przeszukane. ' . $ruinId . ' ' . $parentArmyId);
                    return;
                }

                $find = Application_Model_Database::searchRuin($dataIn['gameId'], $ruinId, $heroId, $parentArmyId, $dataIn['playerId'], $db);

                if (Application_Model_Database::ruinExists($dataIn['gameId'], $ruinId, $db)) {
                    $ruin = array(
                        'ruinId' => $ruinId,
                        'empty' => 1
                    );
                } else {
                    $ruin = array(
                        'ruinId' => $ruinId,
                        'empty' => 0
                    );
                }

                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Application_Model_Database::getArmyById($dataIn['gameId'], $parentArmyId, $db),
                        'ruin' => $ruin,
                        'find' => $find
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'turn':
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => Application_Model_Turn::next($dataIn['gameId'], $dataIn['playerId'], $dataIn['color']),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId']);

                $this->sendToChannel($token, $users);
                break;

            case 'fightNeutralCastle':
                $parentArmyId = $dataIn['data']['armyId'];
                $x = $dataIn['data']['x'];
                $y = $dataIn['data']['y'];
                $castleId = $dataIn['data']['castleId'];

                if (!Zend_Validate::is($parentArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits') || !Zend_Validate::is($castleId, 'Digits')) {
                    echo('Brak "armyId" lub "x" lub "y" lub "castleId"!');
                    return;
                }
                $castle = Application_Model_Board::getCastle($castleId);
                if (empty($castle)) {
                    echo('Brak zamku o podanym ID!');
                    return;
                }
                if (($x < $castle['position']['x']) || ($x >= ($castle['position']['x'] + 2)) || ($y < $castle['position']['y']) || ($y >= ($castle['position']['y'] + 2))) {
                    echo('Na podanej pozycji nie ma zamku!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $army = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);
                if (empty($army)) {
                    echo('Brak armii o podanym ID!');
                    return;
                }
                $distance = $this->calculateArmiesDistance($x, $y, $army['x'], $army['y'], $castleId);
                if ($distance >= 2.5) {
                    echo('Wróg znajduje się za daleko aby można go było atakować (' . $distance . '>=2.5).');
                    return;
                }
                $movesSpend = 2;
                if ($movesSpend > $army['movesLeft']) {
                    echo('Armia ma za mało ruchów do wykonania akcji(' . $movesSpend . '>' . $army['movesLeft'] . ').');
                    return;
                }
                $battle = new Game_Battle($army, null, $dataIn['gameId']);
                $battle->fight();
                $battle->updateArmies($dataIn['gameId'], $db);
                $defender = $battle->getDefender();

                if (empty($defender['soldiers'])) {
                    $res = Application_Model_Database::addCastle($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                    if ($res == 1) {
                        $movesAndPosition = array(
                            'x' => $x,
                            'y' => $y,
                            'movesSpend' => $movesSpend
                        );
                        $res = Application_Model_Database::updateArmyPosition($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $movesAndPosition, $db);
                        switch ($res)
                        {
                            case 1:
                                $response = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);
                                $response['victory'] = true;
                                break;
                            case 0:
                                echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                                break;
                            case null:
                                echo('Zapytanie zwróciło błąd');
                                break;
                            default:
                                echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                                break;
                        }
                    } else {
                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.' . $res);
                    }
                } else {
                    Application_Model_Database::destroyArmy($dataIn['gameId'], $army['armyId'], $dataIn['playerId'], $db);
                    $response = $defender;
                    $response['victory'] = false;
                }
                $response['castleId'] = $castleId;
                $response['battle'] = $battle->getResult();
                $response['x'] = $x;
                $response['y'] = $y;

                $token = array(
                    'type' => $dataIn['type'],
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color'],
                    'data' => $response
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'fightEnemyCastle':
                $parentArmyId = $dataIn['data']['armyId'];
                $x = $dataIn['data']['x'];
                $y = $dataIn['data']['y'];
                $castleId = $dataIn['data']['castleId'];
                if ($parentArmyId === null || $x === null || $y === null || $castleId === null) {
                    echo('Brak "armyId" lub "x" lub "y"!');
                    return;
                }
                $castle = Application_Model_Board::getCastle($castleId);
                if (empty($castle)) {
                    echo('Brak zamku o podanym ID!');
                    return;
                }
                if (($x < $castle['position']['x']) || ($x >= ($castle['position']['x'] + 2)) || ($y < $castle['position']['y']) || ($y >= ($castle['position']['y'] + 2))) {
                    echo('Na podanej pozycji nie ma zamku!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $army = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);
                if (empty($army)) {
                    echo('Brak armii o podanym ID!');
                    return;
                }
                $distance = $this->calculateArmiesDistance($x, $y, $army['x'], $army['y'], $castleId);
                if ($distance >= 2.5) {
                    echo('Wróg znajduje się za daleko aby można go było atakować (' . $distance . '>=2.5).');
                    return;
                }
                $movesSpend = 2;
                if ($movesSpend > $army['movesLeft']) {
                    echo('Armia ma za mało ruchów do wykonania akcji(' . $movesSpend . '>' . $army['movesLeft'] . ').');
                    return;
                }
                if (!Application_Model_Database::isEnemyCastle($dataIn['gameId'], $castleId, $dataIn['playerId'], $db)) {
                    echo('To nie jest zamek wroga.');
                    return;
                }
                $battle = new Game_Battle($army, Application_Model_Database::getAllUnitsFromCastlePosition($dataIn['gameId'], $castle['position'], $db), $dataIn['gameId']);
                $battle->addCastleDefenseModifier($dataIn['gameId'], $castleId, $db);
                $battle->fight();
                $battle->updateArmies($dataIn['gameId'], $db);
                $defender = Application_Model_Database::updateAllArmiesFromCastlePosition($dataIn['gameId'], $castle['position'], $db);
                if (empty($defender)) {
                    $changeOwnerResult = Application_Model_Database::changeOwner($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                    if ($changeOwnerResult != 1) {
                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord. ' . $changeOwnerResult);
                        return;
                    }
                    $movesAndPosition = array(
                        'x' => $x,
                        'y' => $y,
                        'movesSpend' => $movesSpend
                    );
                    $updateArmyPositionResult = Application_Model_Database::updateArmyPosition($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $movesAndPosition, $db);
                    switch ($updateArmyPositionResult)
                    {
                        case 1:
                            $response = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);
                            $response['victory'] = true;
                            break;
                        case 0:
                            echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                            break;
                        case null:
                            echo('Zapytanie zwróciło błąd');
                            break;
                        default:
                            echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                            echo $res;
                            break;
                    }
                } else {
                    Application_Model_Database::destroyArmy($dataIn['gameId'], $army['armyId'], $dataIn['playerId'], $db);
                    $response = $defender;
                    $response['victory'] = false;
                }
                $response['battle'] = $battle->getResult();
                $response['castleId'] = $castleId;
                $response['x'] = $x;
                $response['y'] = $y;

                $token = array(
                    'type' => $dataIn['type'],
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color'],
                    'data' => $response
                );



                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);


                break;

            case 'fightEnemy':
                $parentArmyId = $dataIn['data']['armyId'];
                $x = $dataIn['data']['x'];
                $y = $dataIn['data']['y'];
                $enemyId = $dataIn['data']['enemyArmyId'];
                if ($parentArmyId === null || $x === null || $y === null || $enemyId === null) {
                    echo('Brak "armyId" lub "x" lub "y" lub "$enemyId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $army = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);
                $distance = $this->calculateArmiesDistance($x, $y, $army['x'], $army['y']);
                if ($distance >= 2) {
                    echo('Wróg znajduje się za daleko aby można go było atakować (' . $distance . '>=2).');
                    return;
                }
                $movesSpend = $this->movesSpend($x, $y, $army);
                if ($movesSpend > $army['movesLeft']) {
                    echo('Armia ma za mało ruchów do wykonania akcji (' . $movesSpend . '>' . $army['movesLeft'] . ').');
                    return;
                }
                $enemy = Application_Model_Database::getAllUnitsFromPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $db);
                $battle = new Game_Battle($army, $enemy, $dataIn['gameId']);
                $battle->addTowerDefenseModifier($x, $y);
                $battle->fight();
                $battle->updateArmies($dataIn['gameId'], $db);
                $defender = Application_Model_Database::updateAllArmiesFromPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $db);
                if (empty($defender)) {
                    $movesAndPosition = array(
                        'x' => $x,
                        'y' => $y,
                        'movesSpend' => $movesSpend
                    );
                    $updateArmyPositionResult = Application_Model_Database::updateArmyPosition($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $movesAndPosition, $db);
                    switch ($updateArmyPositionResult)
                    {
                        case 1:
                            $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $parentArmyId, $dataIn['playerId'], $db);

                            $token = array(
                                'type' => $dataIn['type'],
                                'playerId' => $dataIn['playerId'],
                                'color' => $dataIn['color'],
                                'data' => array(
                                    'army' => $attacker,
                                    'enemyArmy' => null,
                                    'battle' => $battle->getResult(),
                                    'victory' => true
                                )
                            );

                            $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                            $this->sendToChannel($token, $users);
                            break;
                        case 0:
                            echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                            break;
                        case null:
                            echo('Zapytanie zwróciło błąd');
                            break;
                        default:
                            echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                            break;
                    }
                } else {
                    Application_Model_Database::destroyArmy($dataIn['gameId'], $army['armyId'], $dataIn['playerId'], $db);

                    $token = array(
                        'type' => $dataIn['type'],
                        'playerId' => $dataIn['playerId'],
                        'color' => $dataIn['color'],
                        'data' => array(
                            'army' => null,
                            'enemyArmy' => $defender,
                            'battle' => $battle->getResult(),
                            'victory' => false,
                        )
                    );

                    $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                    $this->sendToChannel($token, $users);
                }

                break;

            case 'razeCastle':
                $castleId = $dataIn['data']['castleId'];
                if ($castleId == null) {
                    echo('Brak "castleId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $razeCastleResult = Application_Model_Database::razeCastle($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                switch ($razeCastleResult)
                {
                    case 1:
                        $gold = Application_Model_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db) + 1000;
                        Application_Model_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $gold, $db);
                        $response = Application_Model_Database::getCastle($dataIn['gameId'], $castleId, $db);
                        $response['color'] = $dataIn['color'];
                        $response['gold'] = $gold;
                        $token = array(
                            'type' => 'castle',
                            'playerId' => $dataIn['playerId'],
                            'color' => $dataIn['color'],
                            'data' => $response
                        );

                        $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                        $this->sendToChannel($token, $users);
                        break;
                    case 0:
                        echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                        break;
                    case null:
                        echo('Zapytanie zwróciło błąd');
                        break;
                    default:
                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                        break;
                }
                break;

            case 'castleBuildDefense':
                $castleId = $dataIn['data']['castleId'];
                if ($castleId == null) {
                    echo('Brak "castleId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                if (!Application_Model_Database::isPlayerCastle($dataIn['gameId'], $castleId, $dataIn['playerId'], $db)) {
                    echo('Nie Twój zamek.');
                    break;
                }
                $gold = Application_Model_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db);
                $defenseModifier = Application_Model_Database::getCastleDefenseModifier($dataIn['gameId'], $castleId, $db);
                $defensePoints = Application_Model_Board::getCastleDefense($castleId);
                $defense = $defenseModifier + $defensePoints;
                $costs = 0;
                for ($i = 1; $i <= $defense; $i++)
                {
                    $costs += $i * 100;
                }
                if ($gold < $costs) {
                    echo('Za mało złota!');
                    return;
                }
                $buildDefenseResult = Application_Model_Database::buildDefense($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                switch ($buildDefenseResult)
                {
                    case 1:
                        $response = Application_Model_Database::getCastle($dataIn['gameId'], $castleId, $db);
                        $response['defensePoints'] = $defensePoints;
                        $response['color'] = $dataIn['color'];
                        $response['gold'] = $gold - $costs;
                        Application_Model_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $response['gold'], $db);

                        $token = array(
                            'type' => 'castle',
                            'playerId' => $dataIn['playerId'],
                            'color' => $dataIn['color'],
                            'data' => $response
                        );

                        $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                        $this->sendToChannel($token, $users);
                        break;
                    case 0:
                        echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                        break;
                    case null:
                        echo('Zapytanie zwróciło błąd');
                        break;
                    default:
                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                        break;
                }
                break;

            case 'computer':
                $db = Application_Model_Database::getDb();
                if (!Application_Model_Database::isGameMaster($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    echo('Nie Twoja gra!');
                    return;
                }
                $playerId = Application_Model_Database::getTurnPlayerId($dataIn['gameId'], $db);
                if (!Application_Model_Database::isComputer($playerId)) {
                    echo('To nie komputer!');
                    return;
                }
                if (!Application_Model_Database::playerTurnActive($dataIn['gameId'], $playerId, $db)) {
                    $response = Application_Model_Computer::startTurn($dataIn['gameId'], $playerId, $db);
                } else {
                    $army = Application_Model_Database::getComputerArmyToMove($dataIn['gameId'], $playerId, $db);
                    if (!empty($army['armyId'])) {
                        $response = Application_Model_Computer::moveArmy($dataIn['gameId'], $playerId, $army, $db);
                    } else {
                        $response = Application_Model_Computer::endTurn($dataIn['gameId'], $playerId, $db);
                    }
                }

                switch ($response['action'])
                {
                    case 'continue':
                        $type = 'computer';
                        break;
                    case 'end':
                        $type = 'computerEnd';
                        break;
                    case 'gameover':
                        $type = 'computerGameover';
                        break;
                }

                $token = array(
                    'type' => $type,
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color'],
                    'data' => $response
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'open':
                $this->open($user);
                break;
        }
    }

    public function sendToChannel($token, $users) {
        print_r('ODPOWIEDŹ ');
        print_r($token);
        foreach ($users AS $row)
        {
            foreach ($this->users AS $u)
            {
                if ($u->getId() == $row['webSocketServerUserId']) {
                    $this->send($u, Zend_Json::encode($token));
                }
            }
        }
    }

    public function open($user) {
        $token = array(
            'type' => 'open',
            'wssuid' => $user->getId()
        );
        $re = new WebSocket_Message();

        $re->setData(Zend_Json::encode($token));
        $user->sendMessage($re);
    }

    private function chat($data) {
        $token = array(
            'type' => $data['type'],
            'msg' => $data['data'],
            'playerId' => $data['playerId'],
            'color' => $data['color']
        );

        $users = Application_Model_Database::getInGameWSSUIdsExceptMine($data['gameId'], $data['playerId']);

        $this->sendToChannel($token, $users);
    }

    private function move($data) {
        $db = Application_Model_Database::getDb();
        if (!Application_Model_Database::isPlayerTurn($data['gameId'], $data['playerId'], $db)) {
            echo('Nie Twoja tura.');
            return;
        }
        if (isset($data['data']['armyId'])) {
            $armyId = $data['data']['armyId'];
        }
        if (isset($data['data']['x'])) {
            $x = $data['data']['x'];
        }
        if (isset($data['data']['y'])) {
            $y = $data['data']['y'];
        }
        if (!empty($armyId) AND $x !== null AND $y !== null) {

            $mMove = new Application_Model_Move();
            $token = array(
                'type' => $data['type'],
                'data' => $mMove->go($data['gameId'], $armyId, $x, $y, $data['playerId']),
                'playerId' => $data['playerId'],
                'color' => $data['color']
            );

            $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

            $this->sendToChannel($token, $users);
        } else {
            echo('Brak parametrów armii.');
            return;
        }
    }

    private function calculateArmiesDistance($dX, $dY, $aX, $aY, $castleId = null) {
        echo '$dX,$dY=' . $dX . ',' . $dY . ' $aX,$aY=' . $aX . ',' . $aY;
        $distance = sqrt(pow($dX - $aX, 2) + pow($aY - $dY, 2));
        var_dump($distance);
        if ($castleId) {
            $tmp = sqrt(pow($dX + 1 - $aX, 2) + pow($aY - $dY, 2));
            var_dump($tmp);
            if ($tmp < $distance) {
                $distance = $tmp;
            }
            $tmp = sqrt(pow($dX - $aX, 2) + pow($aY - $dY + 1, 2));
            var_dump($tmp);
            if ($tmp < $distance) {
                $distance = $tmp;
            }
            $tmp = sqrt(pow($dX + 1 - $aX, 2) + pow($aY - $dY + 1, 2));
            var_dump($tmp);
            if ($tmp < $distance) {
                $distance = $tmp;
            }
            var_dump($distance);
        }

        return $distance;
    }

    private function movesSpend($x, $y, $army) {
        $canFly = 1;
        $canSwim = 0;
        $movesRequiredToAttack = 1;
        $canFly -= count($army['heroes']);
//        foreach ($army['heroes'] as $hero) {
//            $canFly--;
//        }
        foreach ($army['soldiers'] as $soldier)
        {
            if ($soldier['canFly']) {
                $canFly++;
            } else {
                $canFly -= 200;
            }
            if ($soldier['canSwim']) {
                $canSwim++;
            }
        }
        $fields = Application_Model_Board::getBoardFields();
        $terrainType = $fields[$y][$x];
        $terrain = Application_Model_Board::getTerrain($terrainType, $canFly, $canSwim);
        return $terrain[1] + $movesRequiredToAttack;
    }

}

/**
 * Demo socket server. Implements the basic eventlisteners and attaches a resource handler for /echo/ urls.
 *
 *
 * @author Chris
 *
 */
class WofSocketServer implements IWebSocketServerObserver {

    protected $debug = true;
    protected $server;

    public function __construct() {
        $this->server = new WebSocket_Server('tcp://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort, 'superdupersecretkey');
        $this->server->addObserver($this);

        $this->server->addUriHandler('wof', new WofHandler());
    }

    public function onConnect(IWebSocketConnection $user) {
        $this->say("[DEMO] {$user->getId()} connected");
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
//        $this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
    }

    public function onDisconnect(IWebSocketConnection $user) {
        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
        $this->say("[DEMO] Admin Message received!");

        $frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
        $user->sendFrame($frame);
    }

    public function say($msg) {
        echo "$msg \r\n";
    }

    public function run() {
        $this->server->run();
    }

}

// Start server
$server = new WofSocketServer();
$server->run();

exit;
