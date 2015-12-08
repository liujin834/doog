<?php
namespace Sookon\Mail;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Template implements ServiceManagerAwareInterface
{
    protected $serviceManager;

    private $db;

    const DEFAULT_TEMPLATE_TYPE = 'text';

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->init();

        return $this;
    }

    private function init()
    {
        $dbService = $this->serviceManager->get('Db');
        $this->db = $dbService->getPdo();
    }

    public function fetchAll()
    {
        $sql='SELECT * FROM emailtext';
        $rs=$this->db->query($sql);
        return $rs->fetchAll(\PDO::FETCH_ASSOC);
    }

    //插入邮件模板
    public function insert($data)
    {
        $temp=$this->fetch($data['template']);
        if(isset($temp['id']) && is_numeric($temp['id']) && $temp['id']>0)
        {
            return '该邮件模板标识已经存在，请更换标识！';
        }

        $dbhService = $this->serviceManager->get('Db');
        $dbh = $dbhService->getDbh();

        if(empty($data['subject']))
        {
            $data['subject']='未命名模板';
        }


        $rs = $dbh->insert('emailtext',$data,1);
        return $rs;
    }

    //删除邮件模板
    public function del($id)
    {
        $sql = "DELETE FROM emailtext WHERE id=$id";
        $rs = $this->db->exec($sql);
        return $rs;
    }

    //更新邮件模板
    public function update($data,$id)
    {
        $temp=$this->fetch($data['template']);
        if(isset($temp['id']) && is_numeric($temp['id']) && $temp['id']>0)
        {
            if($id!=$temp['id'])
                return '该邮件模板标识已经存在，请更换标识！';
        }

        $dbhService = $this->serviceManager->get('Db');
        $dbh = $dbhService->getDbh();


        $rs = $dbh->update('emailtext',$data,"id=$id",1);
        return  $rs;
    }

    public function fetch($key)
    {
        if(is_numeric($key))
        {
            $sql="SELECT * FROM emailtext WHERE id=$key";
        }else
        {
            $sql="SELECT * FROM emailtext WHERE template='$key'";
        }

        $rs=$this->db->query($sql);

        return $rs->fetch();
    }

    /**
     * @param $key
     * @param $data
     * @return array
     */
    public function load($key,$data){

        $template_data = $this->fetch($key);

        if(empty($data) || !is_array($data) || count($data) < 1)
            return [
                'subject' => $template_data['subject'],
                'body' => $template_data['body'],
                'type' => isset($template_data['type']) ? $template_data['type'] : self::DEFAULT_TEMPLATE_TYPE
            ];

        $patterns = array();
        $replacements = array();

        foreach($data as $k=>$v)
        {
            $patterns[]='/{'.$k.'}/i';
            $replacements[]=$v;
        }

        ksort($patterns);
        ksort($replacements);

        $replaced_body = preg_replace($patterns, $replacements, $template_data['body']);
        $replaced_subject = preg_replace($patterns, $replacements, $template_data['subject']);

        return [
            'subject' => $replaced_subject,
            'body' => $replaced_body,
            'type' => isset($template_data['type']) ? $template_data['type'] : self::DEFAULT_TEMPLATE_TYPE
        ];

    }//function load

}