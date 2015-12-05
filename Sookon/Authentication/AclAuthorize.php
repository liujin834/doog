<?php
namespace Sookon\Authentication;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;

class AclAuthorize
{
	public $acl;
	public $role;
	
	function __construct(Acl &$acl,$config = "")
	{			
		$this->acl = $acl;
		
		$this->loadAuthorize($config);
		
		$acl = $this->acl;
	}
	
	public function loadAuthorize($config = "")
	{
        $this->config = include(CONFIG_PATH.'/auth/acl_authorize.php');

        foreach($this->config as $k=>$auth)
        {
            if(!isset($auth['inherit']) || empty($auth['inherit']))
                $this->acl->addRole(new Role($auth['alias']));
            else
                $this->acl->addRole(new Role($auth['alias']),$this->config[$auth['inherit']]['alias']);


            if( isset($auth['allow']) && is_array($auth['allow']))
            {
                foreach($auth['allow'] as $index => $allow)
                {
                    if(is_numeric($index))
                        $this->acl->allow($auth['alias'], $allow);
                    else
                        $this->acl->allow($auth['alias'], $index , $allow);

                }
            }

            if( isset($auth['allow']) && is_string($auth['allow']) && $auth['allow'] == 'all')
            {
                $this->acl->allow($auth['alias']);
            }

            if( isset($auth['deny']) && is_array($auth['deny']) )
            {
                foreach($auth['deny'] as $index => $deny)
                {
                    if(is_numeric($index))
                        $this->acl->deny($auth['alias'], $deny);
                    else
                        $this->acl->deny($auth['alias'], $index , $deny);
                }
            }


        }//foreach
		

	}
}