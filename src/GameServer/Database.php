<?php
namespace GameServer;
class Database
{

    static public function getDb()
    {
        $configFile = APPLICATION_PATH . '/configs/application.ini';
//        if (!is_readable($configFile)) {
//            throw new \Application_Exception('Config file "' . $configFile . '" is not readable');
//        }
        $config = new \Zend_Config_Ini($configFile, APPLICATION_ENV);

        $params = $config->resources->db->params;
        return new \Zend_Db_Adapter_Pdo_Pgsql(array(
            'host' => $params->host,
            'username' => $params->username,
            'password' => $params->password,
            'dbname' => $params->dbname
        ));
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param int $gameId
     * @param int $playerId
     * @param string $data
     * @return mixed
     */
    static public function addTokensIn(\Zend_Db_Adapter_Pdo_Pgsql $db, $gameId, $playerId, $token)
    {
        $data = array(
            'playerId' => $playerId,
            'gameId' => $gameId,
            'type' => $token['type']
        );

        unset($token['type']);

        $data['data'] = \Zend_Json::encode($token);

        try {
            return $db->insert('tokensin', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param int $gameId
     * @param int $playerId
     * @param string $data
     * @return mixed
     */
    static public function addTokensOut(\Zend_Db_Adapter_Pdo_Pgsql $db, $gameId, $token)
    {
        $data = array(
            'gameId' => $gameId,
            'type' => $token['type']
        );

        unset($token['type']);

        $keys = array(
            'attackerColor',
            'attackerArmy',
            'defenderColor',
            'defenderArmy',
            'path',
            'battle',
            'oldArmyId',
            'deletedIds',
            'victory',
            'castleId',
            'ruinId',
            'lost',
            'win',
            'gold',
            'costs',
            'income',
            'armies',
            'nr',
            'action',
            'color',
            'x',
            'y',
        );

        foreach ($keys as $key) {
            self::prepareGameHistoryData($key, $data, $token);
        }

        $data['data'] = Zend_Json::encode($token);

        try {
            return $db->insert('tokensout', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function prepareGameHistoryData($value, &$data, &$token)
    {
        if (array_key_exists($value, $token)) {
            if (is_array($token[$value])) {
                $data[$value] = \Zend_Json::encode($token[$value]);
            } elseif (is_bool($token[$value])) {
                if ($token[$value]) {
                    $data[$value] = 't';
                } else {
                    $data[$value] = 'f';
                }
            } else {
                $data[$value] = $token[$value];
            }

            unset($token[$value]);
        }
    }
}
