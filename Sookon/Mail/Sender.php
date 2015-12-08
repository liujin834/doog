<?php
/**
 * Created by PhpStorm.
 * User: Li Jianxuan
 * Date: 14-9-19
 * Time: 下午3:43
 */

namespace Sookon\Mail;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Sookon\Service\ServiceManager as WestdcServiceManager;

class Sender implements ServiceManagerAwareInterface{

    protected $serviceManager;

    public $debug = 0;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    public function __construct()
    {
        if(!$this->serviceManager instanceof ServiceManager)
        {
            $serviceManager = WestdcServiceManager::getInstance();
            $this->serviceManager = $serviceManager->getServiceManager();
        }
    }

    /**
     * 发送即时邮件
     * @param $options
     * @return bool
     */
    public function backend($options)
    {
        $cmd = "php ".CURRENT_BOOTSTRAP_SCRIPT;
        $cmd .= ' mail send';
        $cmd .= ' --email="'.$options['email'].'"';
        $cmd .= ' --name="'.$options['name'].'"';
        $cmd .= ' --template="'.$options['template'].'"';

        if(isset($options['data']))
        {
            $data = json_encode($options['data']);
            $cmd .= ' --data=\''.$data.'\'';
        }

        $tools = $this->serviceManager->get('Tools');

        if($this->debug == 0)
        {
            $tools->execBackend($cmd);
            return true;
        }

        var_dump($tools->execFront($cmd));
        return true;
    }

    /**
     * 将邮件添加到发送列队，降低内存和cpu消耗，但是用户无法即时收到，适用于通知类型的邮件和大批量发送的邮件
     * @param $options
     * @return bool
     */
    public function queue($options)
    {
        $cmd = "php ".CURRENT_BOOTSTRAP_SCRIPT;
        $cmd .= ' mail queue';
        $cmd .= ' --email="'.$options['email'].'"';
        $cmd .= ' --name="'.$options['name'].'"';
        $cmd .= ' --template="'.$options['template'].'"';

        if(isset($options['data']))
        {
            $data = json_encode($options['data']);
            $cmd .= ' --data=\''.$data.'\'';
        }

        $tools = $this->serviceManager->get('Tools');

        if($this->debug == 0)
        {
            $tools->execBackend($cmd);
            return true;
        }

        var_dump($tools->execFront($cmd));
        return true;
        return true;
    }

} 