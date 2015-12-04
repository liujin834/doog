<?php
/**
 * Created by PhpStorm.
 * User: liujin834
 * Date: 14/12/13
 * Time: 上午11:41
 */

namespace Sookon\Service\ServiceAgent;

use Sookon\Db as SookonDb;
use Sookon\Db\Record\Decorate as RecordDecorate;

class Db {

    public function getZendDb(){
        return SookonDb\Db::getInstance();
    }

    public function getPdo()
    {
        return SookonDb\Pdo::getInstance();
    }

    public function getDbh()
    {
        return new SookonDb\Dbh;
    }

    public function getRecordHandle()
    {
        return new SookonDb\Record\Record;
    }

    public function getSelectHandle()
    {
        return new SookonDb\Record\Select;
    }

    public function getRecordDecorate($decorateName){
        $className = "Sookon\\Db\\Record\\Decorate\\".$decorateName;
        return new $className;
    }
} 