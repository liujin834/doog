<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/10
 * Time: 13:26
 */

namespace Sookon\EventModel\Listeners;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;

class StandardFormListener implements ListenerAggregateInterface{

    protected $listeners = array();
    protected $handle;

    function __construct($handle)
    {
        $this->handle = $handle;
        unset($handle);
    }

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('submit.checkParam', array($this->handle, 'checkParam'), 100);
        $this->listeners[] = $events->attach('submit.processData', array($this->handle, 'processData'), 100);
        $this->listeners[] = $events->attach('submit.recordPosted', array($this->handle, 'recordPosted'), 100);
        $this->listeners[] = $events->attach('submit.recordChanged', array($this->handle, 'recordChanged'), 100);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

} 