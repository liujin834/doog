<?php
namespace Sookon\Db;

use Zend\Config\Config as Zend_Config;

class Pdo
{

    private static $_instance = NULL;
	
	private function __construct($DSN = NULL)
    {

    }

    public static function getInstance()
    {

        if (self::$_instance === null) {
            $config_local = new Zend_Config(include "config/autoload/local.php");

            $dsn = "pgsql:host={$config_local->db->hostname};"
                . "port={$config_local->db->port};"
                . "dbname={$config_local->db->database};"
                . "user={$config_local->db->username};"
                . "password={$config_local->db->password}";
            self::$_instance = new \PDO($dsn);
        }

        return self::$_instance;
    }

}