<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/10
 * Time: 13:36
 */

namespace Sookon\EventModel;

use Sookon\EventModel\Handles;
use Sookon\Service\AbstractServiceManager;

class HandleFactory extends AbstractServiceManager{

    public function get($handleName)
    {
        $config = $this->getServiceManager()->get('Config');

        $appConfig = $config->get('application.ini');

        $handleName = $appConfig['HandlesNamespace'] . "\\" . $handleName;

        if(class_exists($handleName))
        {
            return new $handleName();
        }else{
            throw new \RuntimeException("Handle not exists");
        }

    }

} 