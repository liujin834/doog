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
use Sookon\EventModel\AbstractEventManager;
use Sookon\File\Listener\DefaultFileUploadListener;

class Upload extends AbstractEventManager implements ServiceManagerAwareInterface{

    protected $serviceManager;
    protected $defaultListener = false;
    protected $returnInPreCheckTrigger = true;

    private $uploadPath = "";
    private $relativePath = "";
    private $fileName = "";
    private $fileUuid;
    private $config;
    private $params;
    private $rootPath,$childPath,$datePathMode;

    //日期路径模式
    const DATETIME_MODEL_YMD = "Y/M/D/";
    const DATETIME_MODEL_YM = "Y/M/";
    const DATETIME_MODEL_Y = "Y/";

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->init();

        return $this;
    }

    public function init()
    {
        $configService = $this->serviceManager->get('ConfigService');
        $this->config = $configService->get('file.php');
    }

    public function __invoke($files,$rootDir = "",$childDir = "",$fileName = "",$dateDirModel = false)
    {
        return $this->upload($files,$rootDir,$childDir,$fileName,$dateDirModel);
    }

    /**
     * 添加默认侦听器，会将信息保存到Attachments数据表
     */
    public function attachDefaultListener()
    {
        $Listener = new DefaultFileUploadListener;
        $this->getEventManager()->attachAggregate($Listener);
        $this->defaultListener = true;
    }

    /**
     * 上传文件
     * @param $files                    上传文件的信息 e.g.$_FILE['fileData']
     * @param string $rootDir           文件存储的根路径(磁盘物理路径)，如果没有设置则从 config/autoload/file.php 中读取 upload 配置
     * @param string $childDir          根路径中的下一级路径,默认为空
     * @param string $fileName          新文件的文件名，如果不指定则设置为新的UUID
     * @param string|bool $dateDirModel      年月日目录模式，如果设为 false 则存储时不添加年月日路径，年月日路径在子文件夹中创建,unix和linux中需要开启上传目录的权限，否则无法创建文件夹
     * @return array
     */
    public function upload($files,$rootDir = "",$childDir = "",$fileName = "",$dateDirModel = "")
    {
        if (empty($files) !== false) {
            return array("error"=>"请选择要上传的文件.");
        }

        if (is_uploaded_file($files['tmp_name']) === false) {
            return array("error"=>"文件上传失败,请重新上传");
        }

        $file = $files;

        $results = $this->getEventManager()->trigger('upload.pre', $this, compact('file'));

        if($this->returnInPreCheckTrigger === true)
        {
            $cache_data = $results->last();

            if($cache_data !== true)
            {
                return $cache_data;
            }
        }

        $fileService = $this->serviceManager->get('File');

        if(!empty($rootDir))
            $this->setRootDir($rootDir);

        $this->MakeRootDir();

        if(!empty($childDir))
            $this->setChildDir($childDir);

        //$this->MakeChildDir($childDir);

        if(!empty($dateDirModel))
            $this->setDatePathMode($dateDirModel);

        $this->makeDateDir();

        if(empty($this->fileName) && empty($fileName))
            $this->setFileName(NULL , $fileService->getFileTextExt($files['name']));

        if(!empty($fileName))
            $this->setFileName($fileName , $fileService->getFileTextExt($files['name']));

        //移动文件
        $file_path = $this->getUploadPath() . $this->getFileName();

        if (move_uploaded_file($file['tmp_name'], $file_path) === false) {
            return array("error"=>"上传失败，请重试");
        }

        $file_data = array();

        $file_data['file_url'] = $this->getRelativePath() . $this->getFileName();
        $file_data['file_size'] = $files['size'];
        $file_data['db_path'] = $this->getRelativePath() . $this->getFileName();
        $file_data['realname'] = $files['name'];
        $file_data['file_ext'] = $fileService->getFileTextExt($files['name']);
        $file_data['file_mime'] = $fileService->getFileMime($file_path);
        $file_data['uuid'] = $this->fileUuid;

        if(!empty($this->params) && is_array($this->params))
        {
            $file_data = array_merge($file_data,$this->params);
        }

        $results = $this->getEventManager()->trigger('upload.after', $this, compact('file_data'));
        $cache_data = $results->last();

        if(is_array($cache_data))
            $file_data = array_merge($file_data , $cache_data);

        return $file_data;
    }//文件上传

    /**
     * 设置上传根目录
     * @param string $path
     */
    public function setRootDir($path = ""){
        $this->rootPath = $path;

        if(empty($this->rootPath))
            $this->uploadPath = $this->config->upload;
        else
            $this->uploadPath = $this->rootPath;

        if(!preg_match("/[\/|\\\]+$/",$this->uploadPath))
            $this->uploadPath .= "/";
    }

    /**
     * 获取上传文件的根路径
     * 根路径需要自行创建
     * 路径结尾必须加 "/"
     * @return bool
     */
    public function MakeRootDir()
    {
        if(!file_exists($this->rootPath)){
            mkdir($this->rootPath);
        }
    }

    public function setChildDir($dir = ""){

        if(empty($dir)) {
            return true;
        }

        if (!preg_match("/[\/|\\\]+$/", $dir))
            $dir .= "/";

        $this->uploadPath .= $dir;
        if(!file_exists($this->uploadPath)) {
            if(!mkdir($this->uploadPath))
                return "failed to create folder :".$this->uploadPath;
        }

        $this->childPath = $dir;
        $this->relativePath .= $this->childPath;
    }

    public function appendChildDir($dir = ""){

        if(empty($dir)) {
            return true;
        }

        if (!preg_match("/[\/|\\\]+$/", $dir))
            $dir .= "/";

        $this->uploadPath .= $dir;

        if(!file_exists($this->uploadPath)) {
            if(!mkdir($this->uploadPath))
                return "failed to create folder :".$this->uploadPath;
        }
        $this->childPath .= $dir;
        $this->relativePath .= $dir;

    }

    /**
     * 设置子路径，自动加在根路径之后
     * 如果不存在程序将创建
     * @return bool | string
     */
    public function MakeChildDir($dir)
    {
        if(empty($dir)) {
            return true;
        }

        if (!preg_match("/[\/|\\\]+$/", $dir))
            $dir .= "/";

        $this->uploadPath .= $dir;
        if(!file_exists($this->uploadPath)) {
            if(!mkdir($this->uploadPath))
                return "failed to create folder :".$this->uploadPath;
        }

        $this->relativePath = $dir;
    }

    /**
     * 创建并返回年月日的子目录路径
     * @param string $model
     * @return string|bool
     */
    public function makeDateDir($model = "")
    {
        if($model == ''){
            $model = $this->getDatePathMode();
        }

        $current_path = "";

        if(empty($model) || $model == false)
            return $current_path;

        $y = date("Y");
        $m = date("m");
        $d = date("d");

        if($model == self::DATETIME_MODEL_YMD || $model == self::DATETIME_MODEL_YM || $model == self::DATETIME_MODEL_Y) {
            $current_path = $y . "/";
            if (!file_exists($this->uploadPath . $current_path))
                mkdir($this->uploadPath . $current_path);
        }

        if($model == self::DATETIME_MODEL_YMD || $model == self::DATETIME_MODEL_YM){
            $current_path .= $m . "/";
            if (!file_exists($this->uploadPath . $current_path))
                mkdir($this->uploadPath . $current_path);
        }

        if($model == self::DATETIME_MODEL_YMD) {
            $current_path .= $d ."/";
            if (!file_exists($this->uploadPath . $current_path))
                mkdir($this->uploadPath . $current_path);
        }

        $this->uploadPath .= $current_path;
        $this->relativePath .= $current_path;
        return $current_path;
    }

    /**
     * @param $fileName
     * @param $fileExt
     */
    public function setFileName($fileName,$fileExt = "")
    {
        $tools = $this->serviceManager->get('Tools');
        $this->fileUuid = $uuid = $tools->uuid();

        if(!empty($fileName)){
            $this->fileName = $fileName;
            return;
        }

        if(empty($fileExt))
            $this->fileName = $uuid;
        else
            $this->fileName = $uuid . "." . $fileExt;

        return;
    }


    /**
     * @param $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * 强制关闭文件上传前的钩子，默认是所有上传必须执行此钩子已避免上传文件不符合规格
     * 除了后台中特殊的文件操作之外不建议关闭
     */
    public function forceDetachPreCheckTrigger()
    {
        $this->returnInPreCheckTrigger = false;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    public function setDatePathMode($mode){
        $this->datePathMode = $mode;
    }

    public function getDatePathMode(){
        return $this->datePathMode;
    }

    public function getUuid(){
        return $this->fileUuid;
    }

} 