<?php
namespace Sookon\Member;

use Sookon\Helpers\Config;
use Sookon\Db\Pdo as Db;

class Cookie
{
	var $ck='ff08XearZpUkjl3H';
	var $db;  //传入PDO对象
	var $mid; //会员ID
	
	public $scr; //cookie 安全码  $_COOKIE['scr']
	public $user;//cookie User   $_COOKIE['user']
	
	public $srpwd;//执行checkcookie后方可调用
	
	public $memberTable = "users";
	public $FieldUsername = "username";
	public $FieldPasword = "password";
	public $FieldLastlogin = "ts_last_login";
	public $FieldEmail = "email";
	public $FieldLastloginIp = "last_login_ip";
	public $GravatarEmailField = "gravatar_email";
	
	public $RoleMember = "member";

	function __construct()
	{
		$this->db = Db::getInstance();
		$this->config = Config::get();

		if(isset($_COOKIE['scr']) && !empty($_COOKIE['scr']))
		{
			$this->scr = $_COOKIE['scr'];
		}
		if(isset($_COOKIE['user']) && !empty($_COOKIE['user']))
		{
			$this->user= $_COOKIE['user'];
		}	
	}

	
	/**
	 * 检测cookie
	 */
	public function checkcookie()
	{
		$uname = $this->user;
	    $hash  = $this->scr;

	    if(!empty($uname) && !empty($hash))
	    {
		    if (preg_match("/[<|>|#|$|%|^|*|(|)|{|}|'|\"|;|:]/i",$uname) || preg_match("/[<|>|#|$|%|^|*|(|)|{|}|'|\"|;|:]/i",$hash))
		    {
			     $this->mid=0;
			     return false;
		    }
		    else{
		    	$sql = "select {$this->FieldUsername} as userid,{$this->FieldPasword} as pwd from {$this->memberTable} where {$this->FieldUsername}='$uname'";
		    	$rs  = $this->db->query($sql);
		    	$row = $rs->fetch();
		    	$scr = $this->makescr($row['userid'],$row['pwd']);

		    	if($hash == $scr)
		    	{
		    		$this->srpwd=$row['pwd'];
		    		return true;
		    	}
		    	else {
		    		return false;
		    	}	
		    }//cookie安全
	    }else {
	    	return false;
	    }//exit
	}//function checkcookie

	/**
	 * putcookie
	 *
	 * 登陆成功后放置cookie，包含安全码
	 *
	 * @param $uname
	 * @param $pwd
	 * @param int $time
	 * @return bool
	 */
	public function putcookie($uname,$pwd,$time = 604800)
	{
		try {
		    $scrString = $this->makescr($uname,md5($pwd));//加密验证串:防止用户密码被盗；防止伪造cookie。

		    if(!is_numeric($time))
		    {
		    	$time = 604800;
		    }

		    setcookie('user',$uname,time()+$time,'/');
		    setcookie('scr',$scrString,time()+$time,'/');
		    
		    return true;
	    } catch (Exception $e) {
	    	return false;
	    } 

	}//function putcookie
	
	/**
	 * 生成安全码
	 * 
	 * @param String $u
	 * @param String $p
	 * @return string
	 */
	public function makescr($u,$p)
	{
		return substr(md5($u.$p.$this->ck),3,20);
	}
	
	/**
	 * 清除cookie
	 */
	static function flushcookie()
	{
		setcookie('user','',time()-99999,'/');
		setcookie('scr','',time()-99999,'/');
	}

}