<?php
namespace Sookon\Member;

use Sookon\Helpers\Assist;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;
use Sookon\Helpers\Config;
use Sookon\Db\Dbh as dbh;
use Sookon\Db\Pdo as Db;
use Sookon\Db\Db as Zend_Db;
use Sookon\Mail\Mail;
use Sookon\Member\Listener\AccountListener as Listener;
use Sookon\Member\Listener\PwdListener;

class Account implements EventManagerAwareInterface
{	
	public $memberTable = "users";
	public $FieldUsername = "username";
	public $FieldPasword = "password";
	public $FieldLastlogin = "ts_last_login";
	public $FieldEmail = "email";
	public $FieldLastloginIp = "last_login_ip";
	public $GravatarEmailField = "gravatar_email";
	
	public $RoleMember = "member";
	
	private $db;
	protected $events = NULL;		//事件
	private $config;

	function __construct()
	{
		$this->db = Db::getInstance();
		$this->config = Config::get();
		
		$Listener = new Listener();
		$this->getEventManager()->attachAggregate($Listener);
	}
	
	public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        if (NULL === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }
	
	//获取账号信息,数组
    public function getAccountInfo($id = 0)
    {

        if(is_string($id))
            $sql = "SELECT * FROM ".$this->memberTable." WHERE username='$id'";
        elseif(is_numeric($id))
        {
            if($id == 0)
                $id == Assist::User('id');
            $sql = "SELECT * FROM {$this->memberTable} WHERE id=$id";
        }

        $rs = $this->db->query($sql);
        return $rs->fetch(\PDO::FETCH_ASSOC);
    }

	//注册
	public function register($data)
	{
		$params = compact('data');
        $results = $this->getEventManager()->trigger('register.pre', $this, $params);
		$cache_data = $results->last();
		
		if($cache_data !== true)
		{
			if(!is_array($cache_data))
			{
				return array('error'=>$cache_data);
			}else{
				return $cache_data;
			}
		}
		
        $results = $this->getEventManager()->trigger('register.checkUser', $this, $params);
		$cache_data = $results->last();
		
		if($cache_data !== true)
		{
			if(!is_array($cache_data))
			{
				return array('error'=>$cache_data);
			}else{
				return $cache_data;
			}
		}
		
		$loginData = array(
			'username'=>$data['username'],
			'password'=>$data['password']
		);
		
		$data['password'] = md5($data['password']);
		$data['usertype'] = "guest";
		unset($data['confirm_password']);

		$dbh = new dbh();
		
		$id = $dbh->insert($this->memberTable,$data,true);

		if(!empty($id) && is_numeric($id))
		{
			$this->storeLogin($loginData);
			if(isset($state['success']))
			{
				//$mb = new Member();
				//$mb->putcookie($data[$this->FieldUsername],$data[$this->FieldPasword]);
			}
			$params = compact('data','id');
        	$results = $this->getEventManager()->trigger('register.success', $this, $params);
			return array("success" => 1);
		}else{
			if($id === false)
			{
				return array('error'=>'服务器开小差了，请稍后再试');
			}else{
				return array('error'=>'服务器处理中遇到错误，请联系管理员');
			}
		}
		
	}//register
	
	//登陆
	public function login($data)
	{
        $results = $this->getEventManager()->trigger('login.checkParam', $this, compact('data'));
		$cache_data = $results->last();
		
		if($cache_data !== true)
		{
			if(!is_array($cache_data))
			{
				return array('error'=>$cache_data);
			}else{
				return $cache_data;
			}
		}
		
		$state = $this->storeLogin($data);
		
		if(isset($state['success']))
		{
			//$mb = new Member();
			//$mb->putcookie($data[$this->FieldUsername],md5($data[$this->FieldPasword]));
		}
			
		return $state;	
	}//login
	
	//storeLogin
	private function storeLogin($data,$md5 = true)
	{	
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage($this->config->session_namespace));

		$dbAdapter = Zend_Db::getInstance();
		
		$authAdapter = new \Zend\Authentication\Adapter\DbTable(
			$dbAdapter,
			$this->memberTable,
			$this->FieldUsername,
			$this->FieldPasword
		);
		
		if($md5 === true)
		{
			$password = md5($data[$this->FieldPasword]);
		}else{
			$password = $data[$this->FieldPasword];
		}
		
		$authAdapter
			->setIdentity($data[$this->FieldUsername])
			->setCredential($password)
		;

		$result = $authAdapter->authenticate();
		
		$user = $authAdapter->getResultRowObject(null,array($this->FieldPasword));
		
		if(!$result->isValid())
		{
			return array("error"=>"用户信息验证失败");
		}
		
		$email = $user->email;
		$results = $this->getEventManager()->trigger('login.success.createAvatar', $this, compact('email'));
		$user->avatar = $results->last();
		$auth->getStorage()->write($user);
		
		$id = $user->id;
		$results = $this->getEventManager()->trigger('login.success.updateStatus', $this, compact('id'));
		 
		return array('success'=>1);
	}
	
	public function cookieLogin($data)
	{
        $data = $this->getAccountInfo($data['username']);

        if(!$data)
        {
            return false;
        }

        return $this->storeLogin($data,false);
	}
	
	//注册信息参数
	public function getParam(\Zend_Controller_Request_Abstract $request)
	{
		$data = array(
			'username'=>$request->getParam('username'),
			'password'=>$request->getParam('password'),
			'confirm_password'=>$request->getParam('confirm_password'),
			'email'=>$request->getParam('email'),
			'realname'=>$request->getParam('realname')
		);
		return $data;
	}
	
	//获取用户账户修改参数
	public function getEditParam($request)
	{
		$request = new \Zend\Http\PhpEnvironment\Request;
		
		$type = $request->getPost('type');
		
		if($type == "general")
		{
			$data = array(
				'realname'=>$request->getPost('realname'),
				'signature'=>$request->getPost('signature'),
				'description'=>$request->getPost('description')
			);
		}
		
		if($type == "password")
		{
			$data = array(
				'password' => $request->getPost('password'),
				'password_new'=>$request->getPost('password_new'),
				'password_confirm'=>$request->getPost('password_confirm')
			);
		}
		return $data;
	}
	
	//编辑
	public function edit($data,$type)
	{
		$results = $this->getEventManager()->trigger('edit.checkParam', $this, compact('data','type'));
		$cache_data = $results->last();
		
		if($cache_data !== true)
		{
			return $cache_data;
		}
		
		if($type == "general")
		{
			$data['signature'] = htmlspecialchars($data['signature']);
			$data['description'] = htmlspecialchars($data['description']);
		}else if($type == "password")
		{
			$data['password'] = md5($data['password_new']);
			unset($data['password_new']);
			unset($data['password_confirm']);
		}else{
			return "参数错误";
		}
		
		$dbh = new dbh();
		$uid = Assist::User('id');
		if($dbh->update($this->memberTable,$data," id=$uid") === true)
		{
			return true;
		}else{
			return false;
		}
	}
	
	//找回密码
	public function getMyPassword($email)
	{
		$pwdListener = new PwdListener;
		$this->getEventManager()->attachAggregate($pwdListener);
		
		$results = $this->getEventManager()->trigger('pwd.forgot.checkParam', $this, compact('email'));
		$cache_data = $results->last();
		
		if($cache_data !== true)
		{
			return $cache_data;
		}

		$sql = "SELECT * FROM {$this->memberTable} WHERE email='$email'";
		$rs = $this->db->query($sql);
		$row = $rs->fetch();
		
		if(!isset($row['username']) || empty($row['username']))
		{
			return array('error'=>"此邮箱并未注册",'place'=>'email');
		}	
		
		$salt = md5($email.'---'.$row['username']);
		
		$sql = "UPDATE {$this->memberTable} SET salt='$salt' WHERE id={$row['id']}";
		$state = $this->db->exec($sql);
		
		if($state<1)
		{
			return array('error'=>"处理中出现错误，请重试",'place'=>'email');
		}
		
		$mail_template = "forgotpassword";
		$mail_data = array(
			'name'=>$row['realname'],
			'link'=> Assist::getHostLink().'/account/getpassword/?salt='.$salt
		);
		
		
		try{
			$mail = new Mail();
			
			$mail->loadTemplate($mail_template,$mail_data);
			$mail->addTo($email,$row['realname']);
			$mail->send();
		}catch(Exception $e)
		{
			echo "".$e->getMessage();
		}
		return array("success"=>1);
	}
	
	//重置密码
	public function resetPassword($data)
	{
		$results = $this->getEventManager()->trigger('pwd.reset.checkParam', $this, compact('data'));
		$cache_data = $results->last();

		if($cache_data !== true)
		{
			return $cache_data;
		}
		
		$sql = "SELECT * FROM {$this->memberTable} WHERE salt=?";
		$sth = $this->db->prepare($sql);
		$sth->execute(array($data['salt']));
		$row = $sth->fetch();
		
		if(!isset($row['username']) || empty($row['username']))
		{
			return array('error'=>"您提供的校验码不正确，请重新申请重置密码",'place'=>'confirm_password'); 
		}
		
		if($row['username'] !== $data['username'])
		{
			return array('error'=>"您提供的校验码不正确，请重新申请重置密码",'place'=>'confirm_password'); 
		}
		
		$sql = "UPDATE {$this->memberTable} SET password='".md5($data['password'])."',salt='' WHERE id={$row['id']}";
		$this->db->exec($sql);
		
		$mail_template = "getpassworded";
		$mail_data = array(
			'name'=>$row['realname'],
		);

        try {
            $mail = new Mail();
            $mail->loadTemplate($mail_template, $mail_data);
            $mail->addTo($row['email'], $row['realname']);
            $mail->send();
            return true;
        }catch(\Exception $e){

        }

		
	}
	
}