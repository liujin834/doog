<?php
namespace Sookon\Helpers;

class Config
{

	function __construct()
	{
		//$reader = new \Zend\Config\Reader\Ini();
		//$data   = $reader->fromFile('config/config.ini');
	}
	
	static function get($configName = 'global')
	{
        if(defined(CONFIG_PATH))
        {
            throw new \RuntimeException('Not found the config files path');
        }

        $config_path = CONFIG_PATH;

        if(empty($configName))
            $configName = "global.php";

        if(!preg_match("/(\\/|\\\)$/",$config_path))
        {
            $config_path .= "/";
        }

		$config_names = array(
			'local' => "local.php",
			'global' => "global.php",
			'file' => "file.php"
		);

        $configFile = $config_path .'autoload/' . $config_names[$configName];

        unset($config_path);
        unset($configName);
	
		$config = new \Zend\Config\Config(include $configFile);

		return $config;
	}
	
	
}