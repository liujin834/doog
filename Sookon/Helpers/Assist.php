<?php
namespace Sookon\Helpers;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

class Assist
{	
	function __construct($db = NULL)
	{
		
	}
	
	static function addPaginator($data,$ctl,$limit = 10,$viewPartial = "layout/manager/pagination" )
	{
		$request = $ctl->getRequest();
		$page = $ctl->params()->fromRoute('page');

		if(is_array($data)){
			$data = new \Zend\Paginator\Adapter\ArrayAdapter($data);
		}
//		elseif($data instanceof ){
//
//		}

		$paginator = new \Zend\Paginator\Paginator($data);
		$paginator->setCurrentPageNumber($page)
				  ->setItemCountPerPage($limit)
				  ->setPageRange(6);
		$paginator->setDefaultScrollingStyle('Sliding');
		
		$pagination = $ctl->getServiceLocator()->get('viewhelpermanager')->get('PaginationControl');
        $renderer = $ctl->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');

        $paginator->setView($renderer);

		$pagination->setDefaultViewPartial($viewPartial);
		
		$ctl->ViewModel->setVariable('paginator',$paginator);
	}
	
	static function Msg($type,$content,$url=''){
		$html = '<div class="alert '.$type.'">'."\r\n";
		$html.= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'."\r\n";
		$html.= $content."\r\n";
		$html.= '</div>'."\r\n";
		if(!empty($url))
		{
			if($url == -1){
				$html.= '<script language="javascript">setTimeout("window.history.back(-1);",3000);</script>'."\r\n";
			}else{
				$html.= '<script language="javascript">setTimeout("self.location=\''.$url.'\'",3000);</script>'."\r\n";
			}
		}
		return $html;
	}
	
	static function Error($content,$type='',$url=''){
		if(empty($type)) 
		{
			$AlertType = "alert-danger";
		}else{ 
			$AlertType = $type;
		}
		$html = '<div class="alert '.$AlertType.'" id="Alert-error-box">'."\r\n";
		$html.= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'."\r\n";
		if(!is_array($content)) {
			$html.= $content."\r\n";
		}else{
			$html.= '<ul>'."\r\n";
			foreach($content as $v) {
            	$html.='<li>'.$v.'</li>'."\r\n";
            }
			$html.= '</ul>'."\r\n";
		}
		$html.= '</div>'."\r\n";
		return $html;
	}
	
	static function Dump($data,$exit = true){
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
		if($exit)
		{
			exit();
		}
	}
	
	static function Post($ctl,$message,$url=""){
		
		$ctl->ViewModel->setTemplate("layout/layout/message");
		$ctl->ViewModel->setVariable('message',$message);
		$ctl->ViewModel->setVariable('url',$url);
		
		return $ctl->ViewModel;
	}
	
	static function HttpError($viewModel = NULL,$code = 404){
		if(empty($viewModel))
		{
			$viewModel = new ViewModel();
		}
		$response = new \Zend\Http\PhpEnvironment\Response;
		$response->setStatusCode(404);
		$response->send();
		$viewModel->setVariable('message','This page has been eaten by dinosaurs');
		return $viewModel;
	}
	
	static function getHostLink()
	{
		$protocol = "http";
		if(strpos(strtolower($_SERVER['SERVER_PROTOCOL']),"https"))
		{
			$protocol = "https";
		}
		return $protocol."://".$_SERVER['SERVER_NAME'];
	}
	
	static function isXmlHttpRequest($viewModel = NULL,$request = NULL){
		if(empty($request))
		{
			$request = new \Zend\Http\Request();
		}
		if($request->isXmlHttpRequest())
		{
			if(!empty($viewModel))
			{
				$viewModel->setTerminal(true);
        		return $viewModel;
			}
			return true;
		}else{
			return false;
		}
	}
	
	static function checkOs()
	{
		$uname = strtolower(php_uname());
		if (strpos($uname, "darwin") !== false) {
			return 'osx';
		} else if (strpos($uname, "win") !== false) {
			return 'windows';
		} else if (strpos($uname, "linux") !== false) {
			return 'linux';
		} else {
			return 'other';
		}
	}
	
	static function get_class_name($object = null)
	{
		if (!is_object($object) && !is_string($object)) {
			return false;
		}
	   
		$class = explode('\\', (is_string($object) ? $object : get_class($object)));
		return $class[count($class) - 1];
	}
	
	static function thumbnailDecode($thumbjson,$multi = true,$size = 400,$cut = false,$sizeforce = false)
	{
		$thumb = json_decode($thumbjson,true);
		unset($thumbjson);
		
		if(count($thumb) < 1)
		{
			return NULL;
		}
		
		if($multi)
		{
			$url = array();
			foreach($thumb as $k=>$v)
			{
				if($size == -1)
				{
					$url['source'] = $v['fileurl'];
					if(count($v['thumb']))
					{
						foreach($v['thumb'] as $k=>$v)
						{
							if(file_exists("./public/uploads/".$v['url']))
							{
								$url[$k] = $v['url'];
							}
						}
					}
				}else{
					$url['source'] = $v['fileurl'];
					if(count($v['thumb']))
					{
						foreach($v['thumb'] as $k=>$v)
						{
							if(is_numeric($k) && $size < $k && file_exists("./public/uploads/".$v['url']))
							{
								$url[$k] = $v['url'];
							}
						}
					}
				}
			}
			return $url;
			
		}
		
		if(count($thumb)<1)
		{
			return NULL;
		}
		
		$thumb = $thumb[0];
		
		foreach($thumb['thumb'] as $k=>$v)
		{
			if($cut)
			{
				if(isset($thumb['thumb']['cut']))
				{
					if($sizeforce)
					{
						return file_exists("./public/uploads/".$thumb['thumb']['cut'][$size]['url']) ? $thumb['thumb']['cut'][$size]['url']:$thumb['fileurl'];
					}else{
						if($size > (int)$k)
						{
							return file_exists("./public/uploads/".$v['url']) ? $v['url']:$thumb['fileurl'];
						}
					}
				}else{
					return $thumb['fileurl'];
				}
			}else{
				if(is_numeric($k))
				{
					if($size > $k)
					{
						return file_exists("./public/uploads/".$v['url']) ? $v['url']:$thumb['fileurl'];
					}
				}
			}
		}
		
		return $thumb['fileurl'];
	}
	
	static function sksort($array,$key = NULL,$type=SORT_DESC) {
        $sortArray = array();
       
        foreach($array as $v){
            foreach($v as $key=>$value){
                if(!isset($sortArray[$key])){
                    $sortArray[$key] = array();
                }
                $sortArray[$key][] = $value;
            }
        }
       
        if(array_multisort($sortArray[$key],$type,$array))
        {
            return $array;
        }else{
            return $array;
        }
    }
	
	static function QRcode($data)
	{
		include_once('./vendor/Qrcode/qrlib.php');
		\QRcode::png($data);
	}
}