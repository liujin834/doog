<?php
namespace Sookon\File\Listener;

use Zend\EventManager\EventCollection;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Sookon\Service\ServiceManager;

class DefaultFileUploadListener implements ListenerAggregateInterface
{
	protected $listeners = array();

    protected $serviceManager;

	function __construct()
	{
        $this->serviceManager = ServiceManager::getInstance();
        $this->serviceManager = $this->serviceManager->getServiceManager();
	}
	
	public function attach(EventManagerInterface $events)
    {
		$this->listeners[] = $events->attach('upload.pre', function($e){
            return true;
            return ['error' => '文件格式不在可上传的范围内'];
        }, 100);

		$this->listeners[] = $events->attach('upload.pre', function($e){
            return true;
            return ['error' => '文件大小超出了限制'];
        }, 80);

        $this->listeners[] = $events->attach('upload.after', function($e){

            $file_data = $e->getParam('file_data');

            $authService = $this->serviceManager->get('Auth');

            $data = [
                'filename' => $file_data['db_path'],
                'filetype' => $file_data['file_type'],
                'filedesc' => $file_data['file_mime'],
                'userid' => $authService->getIdentity('id'),
                'filesize' => $file_data['file_size'],
                'realname' => $file_data['realname'],
            ];

            if(isset($file_data['language']))
            {
                $data['language'] = $file_data['language'];
            }

            $dbServices = $this->serviceManager->get('Db');
            $dbh = $dbServices->getDbh();

            $attid = $dbh->insert("attachments" , $data , true);

            if(is_numeric($attid))
                return ['attid' => $attid];

            return false;
        }, 100);


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
