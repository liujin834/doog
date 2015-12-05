<?php
namespace Westdc\User\Handle;

use Westdc\Mail\Mail;
use Westdc\Helpers\Config;
use Westdc\Db\Pdo as Db;
use Zend\EventManager\EventInterface;

class RegisterHandle
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
		$this->db = Db::getInstance();
		$this->config = Config::get();
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
			if(!preg_match("/^[a-zA-Z][a-zA-Z0-9_]{4,15}$/",$data['username']))
			{
				return array('error'=>"用户名应当以字母开头，由字母数字和下划线组成，并且长度在5到16个字符之间",'place'=>'username');
			}
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
		
		if(empty($data['email']))
		{
			return array('error'=>"请输入电子邮箱，作为找回密码和接受通知的联系方式",'place'=>'email');
		}
		
		if (!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$data['email']))
		{
			return array('error'=>"请输入正确的电子邮件，推荐使用QQ邮箱和Gmail邮箱",'place'=>'email');
		}
		
		if(empty($data['realname']))
		{
			return array('error'=>"起个响亮的名号吧",'place'=>'realname');
		}
		
		if(mb_strlen($data['realname'],"UTF-8")>15 )
		{
			return array('error'=>":-(这名号也太长了吧，不要超过15个字哦",'place'=>'realname');
		}
		
		return true;
	}//checkParam
	
	public function checkUser(EventInterface $e){
        
		$data = $e->getParam('data');

		if(!is_array($data))
		{
			return "用户信息验证失败，请重新尝试";
		}
		
		$sql = "SELECT id,{$this->FieldUsername},{$this->FieldEmail} FROM ".$this->tbl_member." WHERE {$this->FieldUsername}='{$data['username']}' OR {$this->FieldEmail}='{$data['email']}'";
		
		$rs = $this->db->query($sql);
		
		$row = $rs->fetch();
		
		if(isset($row['id']) && !empty($row['id']))
		{
			if($row[$this->FieldUsername] == $data['username'])
			{
				return array('error'=>'您的用户名已经注册过账号，您是否<a href="/account/forgotpassword">忘记了密码？</a>','place'=>'username');
			}
			
			if($row[$this->FieldEmail] == $data['email'])
			{
				return array('error'=>'您的邮箱已经注册过账号，请换一个邮箱','place'=>'email');
			}
			
			return array('error'=>'您的用户名或邮箱已经使用过，注册马甲请换一个用户名');
		}
		
		return true;
	}//checkUser
	
	public function registerSuccess(EventInterface $e){
        
		$data = $e->getParam('data');

		if(!is_array($data))
		{
			return false;
		}
		
		$id = $e->getParam('id');
		
		if(!is_numeric($id))
		{
			return false;
		}
		
		$mail_template = "register";
		$mail_data = array(
			'name'=>$data['realname'],
			'content'=>''
		);
		
		$mail = new Mail();
		
		$mail->loadTemplate($mail_template,$mail_data);
		$mail->addTo($data['email'],$data['realname']);
		$mail->send();
		
		return true;
	}//registerSuccess
	
	//邮件内容
	public function getMailContent()
	{
		$sql = "SELECT v.id,v.title,v.thumb,v.status,v.content,m.realname,m.username FROM tbl_voice v
				LEFT JOIN tbl_member m ON v.userid = m.id
				WHERE v.status > 0
				ORDER BY v.id DESC
				LIMIT 5";
		$rs = $this->db->query($sql);
		$latest = $rs->fetchAll();
		
		$content = "";
		
		foreach($latest as $k=>$v)
		{
			if($v['thumb'] != '[]')
			{
				$thumb = json_decode($v['thumb'],true);
				$text = mb_strlen($v['content'],"UTF-8") > 100 ? mb_substr($v['content'],0,100,"UTF-8") : $v['content'];
				$content .= '<p style="width:100%;overflow:hidden;"><img src="http://www.msgfm.com'.$this->config->upload->urlbase.$thumb[0]['thumb'][400]['url'].'" height="100" style="float:left;margin-right:10px;" />'.$v['title']. ' / ' .$v['realname'].'<br />'.$text.'<br /><a href="http://www.msgfm.com/voice/'.$v['id'].'.html">查看播放</a></p>';
			}
		}
		
		return $content;
	}//getMailContent();
	
}