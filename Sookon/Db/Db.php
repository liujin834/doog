<?php
namespace Sookon\Db;

use Zend\Db\Adapter\Adapter;
use Zend\Config\Config as Zend_Config;

class Db
{

	private static $_instance = NULL;

	private function __construct(){

	}

	public static function getInstance(){

		if (self::$_instance === NULL) {

			$config_local = new Zend_Config(include "config/autoload/local.php");

			self::$_instance = new Adapter(array(
				'driver' => $config_local->db->driver,
				'hostname' => $config_local->db->hostname,
				'port' => $config_local->db->port,
				'database' => $config_local->db->database,
				'username' => $config_local->db->username,
				'password' => $config_local->db->password
			));

		}

		return self::$_instance;

	}
	
}