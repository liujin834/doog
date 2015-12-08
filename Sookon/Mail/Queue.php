<?php
/**
 * Created by PhpStorm.
 * User: liujin834
 * Date: 15/1/3
 * Time: 下午9:57
 */

namespace Sookon\Mail;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Sookon\EventModel\AbstractEventManager;

class Queue extends AbstractEventManager implements ServiceManagerAwareInterface{

    protected $serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->init();

        return $this;
    }

    private function init()
    {

    }

    public function add(){

    }

    public function show(){

    }

} 