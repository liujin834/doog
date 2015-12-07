<?php
/**
 * Created by PhpStorm.
 * User: liujin834
 * Date: 14/12/26
 * Time: 下午3:52
 */

namespace Sookon\File;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class File implements ServiceManagerAwareInterface{

    protected $serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    //获取文件扩展名
    public function getFileTextExt($file_name)
    {
        $temp_arr = explode(".", $file_name);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        return $file_ext;
    }

    //获取文件Mime，通过finfo扩展
    public function getFileMime($file_name)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $filetype =  finfo_file($finfo, $file_name) ; //文件mime类型
        finfo_close($finfo);
        return $filetype;
    }

    //获取某个文件的信息
    public function get($id)
    {
        if(!is_numeric($id) || $id<1)
            return false;

        $dbService = $this->serviceManager->get('Db');
        $db = $dbService->getPdo();
        $sql = "SELECT * FROM attachments WHERE id=$id";
        $rs = $db->query($sql);

        return $rs->fetch();
    }

    //删除文件
    public function delete($id)
    {
        if(!is_numeric($id) || $id<1)
            return false;

        $file_info = $this->get($id);

        $basePath = $this->getFileSaveDirByType($file_info['filetype']);

        @unlink($basePath . $file_info['filename']);

        $dbService = $this->serviceManager->get('Db');
        $db = $dbService->getPdo();

        return $db->exec("DELETE FROM attachments WHERE id=$id");
    }

    public function getFileSaveDirByType($fileType)
    {
        $configService = $this->serviceManager->get('ConfigService');

        $basePath = "";

        switch ($fileType){
            case "literature":
                $appConfig = $configService->get('application.ini');
                $basePath = $appConfig['reference_save_path'];
                break;
        }

        if (!preg_match("/[\/|\\\]+$/", $basePath))
            $basePath .= "/";

        return $basePath;
    }

} 