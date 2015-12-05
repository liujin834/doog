<?php
namespace Sookon\Member\Handle;

use Sookon\Db\Pdo;
use Sookon\Helpers\Config;

class PwdHandle
{
	private $db;  			//传入PDO对象
	public $tbl_member = "tbl_member";
	public $FieldUsername = "username";
	public $FieldPasword = "password";
	public $FieldLastlogin = "ts_last_login";
	public $FieldEmail = "email";
	public $FieldLastloginIp = "last_login_ip";
	public $FieldGravatarEmail = "gravatar_email";
	private $DefaultFetchMode = \PDO::FETCH_BOTH;	//默认检索模式，防止出现sdtClass错误
	private $config;		//全局配置
	
	function __construct($db = NULL)
	{
		$this->db = Pdo::getInstance();
		$this->config = Config::get();
	}
	
	public function forgotPwdCheckParam(\Zend\EventManager\EventInterface $e){
        
		$email = $e->getParam('email');
		
		if(empty($email))
		{
			return array('error'=>"请输入电子邮箱，作为找回密码和接受通知的联系方式",'place'=>'email');
		}
		
		if (!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$email))
		{
			return array('error'=>"请输入正确的电子邮件",'place'=>'email');
		}
		
		return true;
	}//checkParam
	
	public function sendGetPasswordMail(\Zend\EventManager\EventInterface $e){
        
		$email = $e->getParam('email');

		
		return true;
	}
	
	public function resetPwdCheckParam(\Zend\EventManager\EventInterface $e)
	{
		$data = $e->getParam('data');
		
		if(empty($data['username']))
		{
			return array('error'=>"请输入用户名",'place'=>'username');
		}
		
		if(empty($data['password']))
		{
			return array('error'=>"请输入密码",'place'=>'password');
		}
		
		if(strlen($data['password']) < 6)
		{
			return array('error'=>"密码长度太短，为了安全最少输入6位哦",'place'=>'password');
		}
		
		if(strlen($data['password']) > 14)
		{
			return array('error'=>"密码太长，亲您记得住吗？不要超过14位哦",'place'=>'password');
		}
		
		if(empty($data['confirm_password']))
		{
			return array('error'=>"请再次输入密码已确认输入正确",'place'=>'confirm_password');
		}
		
		if(md5($data['password']) != md5($data['confirm_password']))
		{
			return array('error'=>"两次输入的密码不同，请重新输入",'place'=>'confirm_password');
		}
		
		return true;
	}
	
}