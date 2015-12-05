<?php
namespace Sookon\Authentication;

use Zend\Permissions\Acl\Acl;
use Sookon\Helpers\Assist;
use Sookon\Member\Cookie;
use Sookon\Member\Account;
use Zend\Mvc\MvcEvent;

class AuthenticationService
{
	private $db;
	
	public $acl;
	
	protected $role;
	
	public $loginRouterName = "login";
	public $logoutRouterName = "logout";

    protected $template_layout;
    protected $template_message;
	
	function __construct()
	{
		//初始化配置
		$this->configure();
		$this->role = new \stdClass();
		$this->role->guest = 'guest';
		
		$user = Assist::User();
		
		if(!$user)
		{
			$this->role->current = 'guest';
		}else{
			$this->role->current = $user->usertype;
		}
	}

    public function switchTemplate(MvcEvent $e){
        $config = $e->getApplication()->getServiceManager()->get('Config');
        if(isset($config['default_member_layout']) && !empty($config['default_member_layout']))
            $this->template_layout = $config['default_member_layout'];
        else
            $this->template_layout = "layout/layout";

        if(isset($config['default_member_message']) && !empty($config['default_member_message']))
            $this->template_message = $config['default_member_message'];
        else
            $this->template_message = "layout/layout/message";
    }
	
	public function run($e)
	{
		$module = $e->getRouteMatch()->getParam('module');
		$namespace = $e->getRouteMatch()->getParam('__NAMESPACE__');
		$controller = $e->getRouteMatch()->getParam('controller');
		$action = $e->getRouteMatch()->getParam('action');

        if($module == 'Engine' && $namespace == 'ConsoleApp')
        {
            return true;
        }
		
		Assist::Dump($e->getRouteMatch()->getMatchedRouteName() . ":" . $controller."-".$action,false);

        $this->switchTemplate($e);

		if($rsp = $this->preCookieCheck($e) !== false)
		{
			return $rsp;
		}
		
		try{
			if(!$this->acl->hasResource($controller))
			{
				$this->badRequest($e);
				return;
			}
			
			if($this->acl->isAllowed($this->role->current,$controller) === true)
			{
				return true;
			}else{
				if($this->acl->isAllowed($this->role->current,$controller,$action) === true)
				{
					return true;
				}else{
					$this->response($e);
				}
			}
		}catch (Exception $error) {
			$this->badRequest($error);
			return;
		}
		
	}
	
	public function preCookieCheck(MvcEvent $e)
	{
		if(!Assist::User())
		{
			$mb = new Cookie;
			//Assist::Dump($mb->checkcookie());
			if($mb->checkcookie())
			{
				$account = new Account();
				$account->cookieLogin(array('username'=>$mb->user));

				$response = $e->getResponse();
				$response->setStatusCode(200);
				$response->sendHeaders();

				$layout = $e->getViewModel();

				$viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
				$partial = $viewHelperManager->get('partial');

				$page_content = $partial(
					$this->template_message,
					array(
						'message' => '您的账号已自动登陆',
						'url'=> [
							['title' => '立即跳转', 'url' => $_SERVER['REQUEST_URI']],
							['title'=>'退出登陆','url'=>$e->getRouter()->assemble(array(), array('name' => $this->logoutRouterName))]
						],
					)
				);

				$layout->setVariable('content',$page_content);
				$layout->setTemplate($this->template_layout);

				$e->stopPropagation();

				return $response;

			}
		}

		return false;
	}
	
	public function response($e)
	{

		//用户已经登录的情况
		if(Assist::User() !== false)
		{
			$this->badRequest($e,403);
			return;
		}

		//没有登录的情况
		if(Assist::isXmlHttpRequest())
		{
			
		}else{

			$response = $e->getResponse();
			$response->setStatusCode(404);
			$response->sendHeaders();
			
			$layout = $e->getViewModel();
			
			$viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
			$partial = $viewHelperManager->get('partial');

			$page_content = $partial(
				$this->template_message,
				array(
					'message' => '请先登陆',
					'url'=> $e->getRouter()->assemble(array(), array('name' => $this->loginRouterName))."?href=".$_SERVER['REQUEST_URI'],
				)
			);
			
			$layout->setVariable('content',$page_content);
			$layout->setTemplate($this->template_layout);
			
			$e->stopPropagation();
			
			return $response;
		}
	}
	
	public function badRequest($e,$type = 404) 
	{	
		$response = $e->getResponse();
        $response->setStatusCode(404);
        $response->sendHeaders();
		
		$layout = $e->getViewModel();
		
		$viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
		$partial = $viewHelperManager->get('partial');
		
		if($type == 404)
		{
			$page_content = $partial(
				'error/404', 
				array(
    				'message' => 'This page has been eaten by dinosaurs',
					'controller'=>$controller = $e->getRouteMatch()->getParam('controller'),
					'display_exceptions' => true,
					'reason' => 'error-controller-invalid',
				)
			);
		}else{
			$page_content = $partial(
				'error/404', 
				array(
    				'message' => '您没有权限访问此页面',
					'controller'=>$controller = $e->getRouteMatch()->getParam('controller'),
					'reason' => 'error-controller-invalid',
					'display_exceptions' => true
				)
			);
		}
		
		$layout->setVariable('content',$page_content);
  		$layout->setTemplate($this->template_layout);
		
		$e->stopPropagation();
		
		return $response;
	}
	
	//加载配置
	public function configure()
	{		
		//初始化ACL
		$this->acl = new Acl();
		$this->acl->deny();
		
		//加载资源
		new AclResource($this->acl);
		
		//加载权限
		new AclAuthorize($this->acl);
	}
	
	
}