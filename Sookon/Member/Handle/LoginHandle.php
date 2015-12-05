<?php
namespace Westdc\User\Handle;

use Westdc\Db\Dbh as dbh;
use Westdc\Db\Pdo as Db;
use Westdc\User\Gravatar;
use Zend\EventManager\EventInterface;

class LoginHandle
{
	private $db;  			//传入PDO对象
	public $tbl_member = "tbl_member";
	public $FieldUsername = "username";
	public $FieldPasword = "password";
	public $FieldLastlogin = "ts_last_login";
	public $FieldEmail = "email";
	public $FieldLastloginIp = "last_login_ip";
	public $FieldGravatarEmail = "gravatar_email";
	
	function __construct()
	{
		$this->db = Db::getInstance();
	}
	
	public function checkParam(EventInterface $e){
        
		$data = $e->getParam('data');
		
		if(!is_array($data))
		{
			return "参数错误";
		}
		
		if(empty($data['username']))
		{
			return array('error'=>"请输入用户名",'place'=>'username');
		}
		
		if(!empty($data['username']))
		{
			if(!preg_match("/^[a-zA-Z][a-zA-Z0-9_]{1,15}$/",$data['username']))
			{
				return array('error'=>"用户名应当以字母开头，由字母数字和下划线组成，并且长度在2到25个字符之间",'place'=>'username');
			}
		}
		
		if(empty($data['password']))
		{
			return array('error'=>"请输入密码",'place'=>'password');
		}
		
		$sql = "SELECT id,{$this->FieldPasword},status FROM {$this->tbl_member} WHERE {$this->FieldUsername}=?";
		$sth = $this->db->prepare($sql);
		$rs = $sth->execute(array($data[$this->FieldUsername]));
		$row = $sth->fetch(\PDO::FETCH_ASSOC);
		
		if(isset($row['id']) && !empty($row['id']))
		{
			if(strlen($row[$this->FieldPasword]) !== 32)
			{
				return array('error'=>"您的密码或因安全原因或其他问题已经被重置，请先<a href='/account/forgotpassword'>重置密码</a>再登陆",'place'=>'password');
			}
			if($row[$this->FieldPasword] !== md5($data['password']))
			{
				return array('error'=>"密码错误",'place'=>'password');
			}
			if($row['status'] < 1 )
			{
				return array('error'=>'您的账号密码状态错误，无法登录');
			}
			return true;
		}else{
			return array('error'=>"用户不存在",'place'=>'username');
		}
		
	}//checkParam
	
	public function updateStatus(EventInterface $e){
		
		$id = (int)$e->getParam('id');
		
		if(!is_numeric($id))
		{
			return false;
		}
		
		$update = array(
			$this->FieldLastlogin => date("Y-m-d H:i:s"),
			$this->FieldLastloginIp => $_SERVER["REMOTE_ADDR"]
		);
		
		$dbh = new dbh();
		@$statusUpdate = $dbh->update($this->tbl_member,$update," id=$id ");
		
		return true;
	}//loginSuccess
	
	public function createAvatar(EventInterface $e){
        
		$email = $e->getParam('email');		
		$avatar = new Gravatar();
		return $avatar->Get($email);
		
	}//loginSuccess
	
	
	
}