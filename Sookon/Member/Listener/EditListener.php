<?php
namespace Sookon\Member\Listener;

use Sookon\Member\Handle\EditHandle;
use Zend\EventManager\EventCollection;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

class EditListener implements ListenerAggregateInterface
{
	protected $listeners = array();
	
	function __construct($type = "")
	{

	}
	
	public function attach(EventManagerInterface $events)
    {
        $_Events = new EditHandle();
		$this->listeners[] = $events->attach('edit.checkParam', array($_Events, 'checkParam'), 100);
		$this->listeners[] = $events->attach('edit.success', array($_Events, 'editSuccess'), 50);
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
