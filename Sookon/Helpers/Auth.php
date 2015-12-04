<?php
namespace Sookon\Helpers;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

class Auth
{
	public $auth;

	function __construct($getAuthService = false)
	{
		$config = Config::get();

		$this->auth = new AuthenticationService();
		$this->auth->setStorage(new SessionStorage($config->session_namespace));
	}
	
	public function getInstance()
	{
		return $this->auth;
	}
	
	public function clearIndentity()
	{
		$this->auth->clearIdentity();
		return true;
	}
	
	public function getIdentity($field = "")
	{
        if(empty($field))
            return $this->auth->getIdentity();

        if(isset($this->auth->getIdentity()->$field))
		    return $this->auth->getIdentity()->$field;
        else
            return null;
	}

    public function write($user)
    {

        if(is_array($user))
            $user = (object)$user;

        $this->auth->getStorage()->write($user);
    }
}