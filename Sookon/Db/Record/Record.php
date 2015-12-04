<?php
/**
 * Created by PhpStorm.
 * User: liujin834
 * Date: 15-2-16
 * Time: 上午11:31
 */

namespace Sookon\Db\Record;

use Zend\Db\Sql;
use Sookon\EventModel\AbstractEventManager;
use Sookon\Db\Db;
use Sookon\Db\Dbh;

class Record extends AbstractEventManager {

    /**
     * 向数据库表中写入新数据
     * @param string $table  表名
     * @param array $data 要写入的数据
     * @param callable $callback 执行结束后的回调函数
     * @param string|array $return 插入后返回的字段
     * @return bool
     * @throws RecordException
     */
    public function insert($table,$data,callable $callback = null,$return = "id"){

        if(!is_string($table) && !$table instanceof Sql\TableIdentifier)
            throw new RecordException("table must is string or TableIdentifier");

        $params = compact('data');

        $this->getEventManager()->trigger(PreDeclare::EVENT_INSERT_PRE, $this, $params);

        /** @var \Zend\EventManager\ResponseCollection $results */
        $results = $this->getEventManager()->trigger(PreDeclare::EVENT_INSERT_PROCESS_DATA, $this, $params);
        if(!$results->isEmpty())
            $data = $results->bottom();

        try{
            $adapter = Db::getInstance();

            $sql = new Sql\Sql($adapter);

            $insert = $sql->insert($table);
            $insert->values($data);

            if(empty($return)){
                $statement = $sql->prepareStatementForSqlObject($insert);
                $result = $statement->execute();

                if(!empty($callback) && is_callable($callback))
                    call_user_func($callback,$data);

                return $result;
            }else{
                $sql_string = $sql->getSqlStringForSqlObject($insert,$adapter->getPlatform());
                $sql_string .= " RETURNING ".$return;
                $result = $adapter->query($sql_string,$adapter::QUERY_MODE_EXECUTE);

                $arr = $result->toArray();

                $var_name = $return;

                if(isset($arr[0][$return])){
                    $var_name = $arr[0][$return];
                }

                if(!empty($callback) && is_callable($callback))
                    call_user_func($callback,$var_name,$data);

                $params = compact('data',$var_name);
                $this->getEventManager()->trigger(PreDeclare::EVENT_INSERT_POST, $this, $params);

                return $result->count();
            }
        }catch(\Exception $e){
            throw new RecordException($e->getMessage());
        }

    }//insert

    /**
     * 更新数据库记录
     * @param $table
     * @param array $data
     * @param string $where
     * @param callable $callback
     * @return bool | \Zend\Db\Sql\Statement;
     * @throws RecordException
     */
    public function update($table,array $data,$where = '',callable $callback = null){

        if(!is_string($table) && !$table instanceof Sql\TableIdentifier)
            throw new RecordException("table must is string or TableIdentifier");

        $params = compact('data');
        $this->getEventManager()->trigger(PreDeclare::EVENT_UPDATE_PRE, $this, $params);

        /** @var \Zend\EventManager\ResponseCollection $results */
        $results = $this->getEventManager()->trigger(PreDeclare::EVENT_UPDATE_PROCESS_DATA, $this, $params);
        if(!$results->isEmpty())
            $data = $results->bottom();

        if($table instanceof Sql\TableIdentifier)
            $table = $table->getSchema().".".$table->getTable();

        if(is_string($where) || empty($where)){
            $dbh = new Dbh;
            $id = $dbh->update($table,$data,$where,true);
        }

        elseif($where instanceof Sql\Where || is_callable($where))
        {
            try{
                $adapter = Db::getInstance();

                $sql = new Sql\Sql($adapter);

                $update = $sql->update($table);
                $update->where($where);
                $update->set($data);

                $statement = $sql->prepareStatementForSqlObject($update);
                $sth = $statement->execute();

                if(!empty($callback) && is_callable($callback))
                    call_user_func($callback,$data);

                return $sth;
            }catch (\Exception $e){
                throw new RecordException($e->getMessage(),$e->getCode());
            }
        }

        elseif(is_array($where))
        {

        }

        else
            throw new RecordException("Condition must is one of string,Zend\\Db\\Sql\\Where object,array");

        if(!empty($callback) && is_callable($callback))
            call_user_func($callback,$data);

        $params = compact('data','id');
        $this->getEventManager()->trigger(PreDeclare::EVENT_UPDATE_POST, $this, $params);

        return true;
    }//update

    /**
     * @param $table
     * @param $where
     * @param callable $callback
     * @return bool
     * @throws RecordException
     */
    public function delete($table,$where,callable $callback = NULL){

        if(!is_string($table) && !$table instanceof Sql\TableIdentifier)
            throw new RecordException("table must is string or TableIdentifier");

        if($table instanceof Sql\TableIdentifier)
            $table = $table->getSchema().".".$table->getTable();

        $params = compact('where','table');

        $this->getEventManager()->trigger(PreDeclare::EVENT_DELETE_PRE, $this, $params);

        $dbAdapter = Db::getInstance();

        $sql = new Sql\Sql($dbAdapter);
        $delete = $sql->delete($table);

        $delete->where($where);

        $queryString = $sql->getSqlStringForSqlObject($delete);
        $status = $dbAdapter->query($queryString);
        $result = $status->execute();

        if($result->getAffectedRows() > 0){
            if(!empty($callback) && is_callable($callback)){
                call_user_func($callback,$result->getAffectedRows());
            }
            return true;
        }else{
            return false;
        }
    }//delete

}