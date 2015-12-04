<?php
namespace Sookon\Service;

use Zend\ServiceManager\ServiceManager as Zend_ServiceManager;

class ServiceManager {

    private static $instance = NULL;

    public static function getInstance()
    {

        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private $serviceManager;

    private function __construct()
    {
        $this->serviceManager = new Zend_ServiceManager;
        $this->serviceManager->addAbstractFactory(new ServiceFactory);

        $configService = $this->serviceManager->get('ConfigService');
        $invoked_services = $configService->get('service.invoked.ini');

        foreach($invoked_services as $k=>$v) {
            $this->serviceManager->setInvokableClass($k, $v);
        }
    }

    public function addKey($key,$value = "")
    {
        if(!empty($value))
            $this->serviceManager->$key($value);
        else
            $this->serviceManager->$key();
    }

    public function setServiceManager(Zend_ServiceManager $service)
    {
        $this->serviceManager = $service;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

} 