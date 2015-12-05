<?php
namespace Westdc\User\Handle;

use Westdc\Helpers\View as view;
use Westdc\Helpers\Pdo;
use Westdc\Helpers\Config;
use Zend\EventManager\EventInterface;

class EditHandle
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
		$this->db = new Pdo;
		$this->config = Config::get();
	}
	
	public function checkParam(EventInterface $e){
        
		$data = $e->getParam('data');
		$type = $e->getParam('type');
		
		if($type == 'general')
		{
		
			if(empty($data['realname']))
			{
				return "起个响亮的名号吧";
			}
			
			if(mb_strlen($data['realname'],"UTF-8")>10 )
			{
				return "这名号也太长了吧，不要超过10个字哦";
			}
		}
		
		if($type == "password")
		{
			if(strlen($data['password'])>18 || strlen($data['password_new'])>18)
			{
				return "密码过长";
			}
			if(strlen($data['password_new'])<=6 || strlen($data['password_confirm'])<=6)
			{
				return "密码过短";
			}
			if(md5($data['password_new']) != md5($data['password_confirm']))
			{
				return "两次输入的密码不同";
			}
			
			$uid = view::User('id');
			$sql = "SELECT {$this->FieldPasword} FROM {$this->tbl_member} WHERE id=$uid";
			$rs = $this->db->query($sql);
			$row = $rs->fetch();
			
			if(md5($data['password']) != $row[$this->FieldPasword])
			{
				return "原密码不正确";
			}
		}
		
		return true;
	}//checkParam
	
	public function editSuccess(EventInterface $e){
        
		$data = $e->getParam('data');

		
		return true;
	}
	
}