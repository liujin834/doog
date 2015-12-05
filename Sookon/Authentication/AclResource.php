<?php
namespace Westdc\Authentication;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class AclResource
{
	public $acl;
	
	public $config;
	
	function __construct(Acl &$acl)
	{			
		$this->acl = $acl;

        $this->config = include(CONFIG_PATH.'/auth/acl_resource.php');
		
		$this->loadResource();
		
		$acl = $this->acl;


	}
	
	/*public function loadResource()
	{
		foreach($this->config as $index => $resource)
		{
			if(!is_array($resource))
			{
				$this->acl->addResource(new Resource($resource));
				continue;
			}
			
			$this->acl->addResource(new Resource($index));
				
			foreach($resource as $controller=>$action)
			{
				$this->acl->addResource(new Resource($controller.'\\'.$action));
			}
		}
	}*/
	
	
	public function loadResource()
	{
		foreach($this->config as $index => $resource)
		{
			if(!is_array($resource))
			{
				$this->acl->addResource(new Resource($resource));
				continue;
			}
			
			$this->acl->addResource(new Resource($index));
			
			foreach($resource as $action)
			{
				if($this->acl->hasResource($action))
				{
					//exit($index."-".$action);
					//$this->acl->addResource($this->acl->getResource($action),$index);
					continue;
				}
				
				//echo $index."-".$action."<br />";
				$this->acl->addResource(new Resource($action),$index);
			}
		}
	}
	
}