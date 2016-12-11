<?php

class Cli_Model_Database
{
    public static function getDb()
    {
        $params = Zend_Registry::get('config')->resources->db->params;

        try {
            $instance = new Zend_Db_Adapter_Pdo_Pgsql(array(
                'host' => $params->host,
                'username' => $params->username,
                'password' => $params->password,
                'dbname' => $params->dbname
            ));
        } catch (PDOException $e) {
            die('Database connection could not be established.');
        }

        return $instance;
    }
}
