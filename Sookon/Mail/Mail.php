<?php
/**
 * Created by PhpStorm.
 * User: Li Jianxuan
 * Date: 14-9-19
 * Time: 下午3:43
 */

namespace Sookon\Mail;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Sookon\EventModel\AbstractEventManager;
use Sookon\Service\ServiceManager as WestdcServiceManager;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Mail extends AbstractEventManager implements ServiceManagerAwareInterface{

    protected $serviceManager;

    public $mail;
    public $config;
    public $subject;
    public $body;
    public $type;
    public $transport;
    public $from;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->init();

        return $this;
    }

    private function init()
    {
        if(!$this->serviceManager instanceof ServiceManager)
        {
            $serviceManager = WestdcServiceManager::getInstance();
            $this->serviceManager = $serviceManager->getServiceManager();
        }

        $this->loadConfigure();
        $this->smtp();
        $this->buildMailMessage();
    }

    //单独调用Mail类的时候需要先执行委托函数
    public function __invoke()
    {
        $this->init();
    }

    public function loadConfigure()
    {
        $configService = $this->serviceManager->get('ConfigService');
        $this->config = $configService->get('email.ini');
    }

    public function smtp()
    {
        $this->transport = new SmtpTransport();

        $options   = new SmtpOptions(array(
            'name'              => $this->config['smtp']['hostname'],
            'host'              => $this->config['smtp']['host'],
            'port'              => $this->config['smtp']['port'], // Notice port change for TLS is 587
            'connection_class'  => $this->config['smtp']['auth'],
            'connection_config' => array(
                'username' => $this->config['smtp']['username'],
                'password' => $this->config['smtp']['password'],
                'ssl'      => $this->config['smtp']['ssl'],
            ),
        ));

        $this->transport->setOptions($options);
    }

    public function buildMailMessage($mail = NULL)
    {
        if(empty($mail))
        {
            $this->mail = new Message();
        }else{
            $this->mail = $mail;
        }

        $this->mail->setEncoding("UTF-8");
    }

    //设置默认发件人
    public function setDefaultFrom()
    {
        $this->mail->setFrom($this->config['smtp']['username'],$this->config['smtp']['name']);
    }

    //添加收件人
    public function addTo($email,$name)
    {
        $this->mail->addTo($email,$name);
    }

    //加载模板
    public function loadTemplate($id,$data){

        $mailTemplate = $this->serviceManager->get('Mail/Template');

        $content = $mailTemplate->load($id,$data);

        $this->subject = $content['subject'];
        $this->body = $content['body'];
        $this->type = $content['type'];

    }//加载模板

    /**
     * @param $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }


    /**
     * @param null $from
     * @return bool
     */
    public function preSend($from = NULL)
    {
        if(empty($this->subject) || empty($this->body))
        {
            return "邮件信息不完整";
        }

        if($this->type == 'html')
        {
            $bodyPart = new MimeMessage();

            $bodyMessage = new MimePart($this->body);
            $bodyMessage->type = 'text/html';

            $bodyPart->setParts(array($bodyMessage));

            $this->mail->setBody($bodyPart);
        }else{
            $this->mail->setBody($this->body);
        }

        if(empty($from) && empty($this->from))
        {
            $this->setDefaultFrom();
        }else{
            if(!empty($this->from))
                $this->mail->setFrom($this->from['email'],$this->from['name']);
            if(!empty($from))
                $this->mail->setFrom($from['email'],$from['name']);
        }

        $this->mail->setSubject($this->subject);

        return true;
    }

    //使用loadTemplate 的结果发送邮件
    //在此之前需要使用 $this->mail->addTo()添加收件人
    /**
     * @param null $from
     * @return bool
     */
    public function send($from = NULL){

        if(!$status = $this->preSend($from))
            return $status;

        try {
            $this->transport->send($this->mail);
            return true;
        }catch(\Exception $e)
        {
            throw new \RuntimeException($e->getMessage());
        }
    }


} 