<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/14
 * Time: 11:43
 */

namespace Sookon\Service\ServiceAgent;

use Sookon\EventModel\ListenerFactory;
use Sookon\EventModel\HandleFactory;

class Event {

    public function getListener($listenerName,$handle = ""){
        $ListenerFactory = new ListenerFactory();
        if(empty($handle))
        {
            return $ListenerFactory->get($listenerName);
        }else{
            return $ListenerFactory->get($listenerName,$handle);
        }
    }

    public function getHandle($handleName = "")
    {
        $handleFactory = new HandleFactory();

        return $handleFactory->get($handleName);

    }

} 