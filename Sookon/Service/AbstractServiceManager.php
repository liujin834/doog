<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/11
 * Time: 11:05
 */

namespace Sookon\Service;

abstract class AbstractServiceManager {

    public function getServiceManager()
    {
        $sm = ServiceManager::getInstance();
        return $sm->getServiceManager();
    }

} 