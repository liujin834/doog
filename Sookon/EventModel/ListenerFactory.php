<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/10
 * Time: 11:46
 */

namespace Sookon\EventModel;

use Sookon\EventModel\Listeners;
use Sookon\Service\AbstractServiceManager;

class ListenerFactory extends AbstractServiceManager{

    public function get($listenerName,$handle = "")
    {
        $config = $this->getServiceManager()->get('Config');

        $appConfig = $config->get('application.ini');

        $listenerName =$appConfig['ListenersNamespace'] . "\\" . $listenerName;

        if(!class_exists($listenerName))
        {
            throw new \RuntimeException("Listener [" . $listenerName . "] not found");
        }

        if(empty($handle))
            return new $listenerName();

        return new $listenerName($handle);
    }

} 