<?php
namespace Sookon\Member\Listener;

use Sookon\Member\Handle\RegisterHandle;
use Sookon\Member\Handle\LoginHandle;
use Zend\EventManager\EventCollection;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

class AccountListener implements ListenerAggregateInterface
{
	private $type;
	
	protected $listeners = array();

	function __construct($type = "")
	{		
		if(empty($type))
		{
			$type = "both";
		}
		
		$this->type = $type;
	}
	
	public function attach(EventManagerInterface $events)
    {
        $_Events = new RegisterHandle();
		$this->listeners[] = $events->attach('register.checkParam', array($_Events, 'checkParam'), 100);
		$this->listeners[] = $events->attach('register.checkUser', array($_Events, 'checkUser'), 80);
		$this->listeners[] = $events->attach('register.success', array($_Events, 'registerSuccess'), 50);
		
		$_Events = new LoginHandle();
		$this->listeners[] = $events->attach('login.checkParam', array($_Events, 'checkParam'), 100);
		$this->listeners[] = $events->attach('login.success.updateStatus', array($_Events, 'updateStatus'), 50);
		$this->listeners[] = $events->attach('login.success.createAvatar', array($_Events, 'createAvatar'), 50);
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
