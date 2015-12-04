<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/4
 * Time: 10:39
 */
namespace Sookon\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractFactoryInterface;

class ServiceFactory implements AbstractFactoryInterface{

    private $invokedService;
    private $invokedNames;
    private $currentServiceType;

    function __construct()
    {
        $this->invokedService = $this->getInvokedServiceFromConfig();
        $this->invokedNames = array_keys($this->invokedService);
    }

    private function getInvokedServiceFromConfig()
    {
        return include dirname(__FILE__) . "/service.lazy.config.php";
    }

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if(!is_array($this->invokedService))
            throw new \RuntimeException('lazy services not found');

        if(in_array($requestedName , $this->invokedNames))
        {
            $this->currentServiceType = "lazy";
            return true;
        }

        $serviceAgentDir = __DIR__ . "/ServiceAgent";

        if(is_dir($serviceAgentDir))
        {
            if(false != ($handle = opendir($serviceAgentDir)))
            {
                while(false !== ($file = readdir($handle)))
                {
                    if(substr($file,0,strlen($file)-4) == (string)$requestedName) {
                        $this->currentServiceType = "agent";
                        return true;
                    }
                }
            }
        }

        return false;

    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        switch($this->currentServiceType)
        {
            case 'lazy':
                $service = new $this->invokedService[$requestedName];

                $service->SERVICE_TYPE = "lazy";
                $service->SERVICE_NAME = $requestedName;

                return $service;

            case 'agent':
                $serviceName = __NAMESPACE__ . "\\ServiceAgent\\" . $requestedName;

                $service = new $serviceName;

                $service->SERVICE_TYPE = "agent";
                $service->SERVICE_NAME = $requestedName;

                return $service;

        }

    }



} 