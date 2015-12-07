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

class Download implements ServiceManagerAwareInterface{

    protected $serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    static public function Stream($file,$name = "",$mime_type = ""){

        $fileChunkSize = 1024*30;

        if(!is_readable($file)) die('File not found or inaccessible!');

        if(empty($name))
            $name = pathinfo($file,PATHINFO_BASENAME);

        if(!empty($name)){
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if(empty($ext))
            {
                $name .= "." . pathinfo($file,PATHINFO_EXTENSION);
            }
        }

        $size = filesize($file);
        $name = filter_var($name,FILTER_SANITIZE_URL);

        if($mime_type == '')
        {
            $mime_type="application/force-download";
        }

        @ob_end_clean();

        if(ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        if(isset($_SERVER['HTTP_RANGE']))
        {
            list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
            list($range) = explode(",",$range,2);
            list($range, $range_end) = explode("-", $range);
            $range=intval($range);
            if(!$range_end)
                $range_end=$size-1;
            else
                $range_end=intval($range_end);

            $new_length = $range_end-$range+1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range-$range_end/$size");
        }
        else
        {
            $new_length=$size;
            header("Content-Length: ".$size);
        }

        $chunk_size = 1*($fileChunkSize);
        $bytes_send = 0;
        if ($file = fopen($file, 'r'))
        {
            if(isset($_SERVER['HTTP_RANGE']))
                fseek($file, $range);

            while(!feof($file) &&
                (!connection_aborted()) &&
                ($bytes_send<$new_length)
            )
            {
                $buffer = fread($file, $chunk_size);
                print($buffer);
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($file);
        }
        else die('Error - can not open file.');

        die();
    }

} 