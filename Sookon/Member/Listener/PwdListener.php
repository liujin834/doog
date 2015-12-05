<?php
namespace Sookon\Member\Listener;

use Sookon\Member\Handle\PwdHandle;
use Zend\EventManager\EventCollection;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

class PwdListener implements ListenerAggregateInterface
{
	protected $listeners = array();
	
	function __construct()
	{

	}
	
	public function attach(EventManagerInterface $events)
    {
        $_Events = new PwdHandle();
		$this->listeners[] = $events->attach('pwd.forgot.checkParam', array($_Events, 'forgotPwdCheckParam'), 100);
		$this->listeners[] = $events->attach('pwd.forgot.sendmail', array($_Events, 'sendGetPasswordMail'), 50);
		$this->listeners[] = $events->attach('pwd.reset.checkParam', array($_Events, 'resetPwdCheckParam'), 100);
		$this->listeners[] = $events->attach('pwd.reset.sendmail', array($_Events, 'sendGetPasswordMail'), 50);
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
