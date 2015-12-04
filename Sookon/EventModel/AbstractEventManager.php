<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/10
 * Time: 11:43
 */

namespace Sookon\EventModel;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

abstract class AbstractEventManager {

    protected $events;

    public function setEventManager (EventManagerInterface $events) {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;

        return $this;
    }

    public function getEventManager () {
        if (NULL === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

} 