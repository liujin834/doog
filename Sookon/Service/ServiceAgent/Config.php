<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/14
 * Time: 11:34
 */

namespace Sookon\Service\ServiceAgent;

use Zend\Config\Config as Zend_Config;
use Zend\Config\Reader\Ini as ReaderIni;

class Config {

    public function get($configName = "")
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

        $configFile = $config_path .'autoload/' . $configName;

        unset($config_path);
        unset($configName);

        if(!file_exists($configFile))
        {
            throw new \RuntimeException('The Config file is not exists');
        }

        $configFileExt = pathinfo($configFile,PATHINFO_EXTENSION);

        if($configFileExt == "php")
        {
            $config_arr = include $configFile;
            return new Zend_Config($config_arr);
        }

        if($configFileExt == 'ini')
        {
            $ini = new ReaderIni();
            $ini->fromFile($configFile);
            return $ini->fromFile($configFile);
        }

    }

} 