<?php
/**
 * Created by PhpStorm.
 * User: liujin834
 * Date: 3/8/15
 * Time: 2:29 PM
 */

namespace Sookon\Db\Record;

use Zend\Db\Sql;
use Sookon\Db\Db;

class Select {

    public function ResultSetToArray(Sql\Select $select){

        $adapter = Db::getInstance();
        $result = $adapter->query($select->getSqlString($adapter->getPlatform()),$adapter::QUERY_MODE_EXECUTE);
        return $result->toArray();

    }

    public function GetSql(Sql\Select $select){
        $adapter = Db::getInstance();
        return $select->getSqlString($adapter->getPlatform());
    }

}