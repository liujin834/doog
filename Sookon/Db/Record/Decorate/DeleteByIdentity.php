<?php
/**
 * Created by PhpStorm.
 * User: liujin834
 * Date: 3/4/15
 * Time: 2:06 PM
 */

namespace Sookon\Db\Record\Decorate;

use Sookon\Db\Record\Record;
use Sookon\Db\Record\RecordException;
use Sookon\Db\Record\PreDeclare;
use Sookon\Db\Db;
use Zend\Db\Sql;

class DeleteByIdentity extends Record{

    protected $identity;
    protected $value;
    protected $table;

    public function setTable($table){
        $this->table = $table;
    }

    public function getTable(){
        return $this->table;
    }

    public function setIdentity($identity){
        $this->identity = $identity;
    }

    public function getIdentity(){
        return $this->identity;
    }

    public function setValue($value){
        $this->value = $value;
    }

    public function getValue(){
        return $this->value;
    }

    /**
     * @param string $table
     * @param callable $callback
     * @return bool
     * @throws RecordException
     */
    public function delete($table = "",callable $callback = NULL){

        if(empty($table))
            throw new RecordException("table name is required");

        if(!is_string($table) && !$table instanceof Sql\TableIdentifier)
            throw new RecordException("table must is string or TableIdentifier");

        if(empty($this->getIdentity()))
            throw new RecordException("identity is null");

        if(empty($this->getValue()))
            throw new RecordException("value is null");

        if($table instanceof Sql\TableIdentifier)
            $table = $table->getSchema().".".$table->getTable();

        $params = compact('where','table');

        $this->getEventManager()->trigger(PreDeclare::EVENT_DELETE_PRE, $this, $params);

        $dbAdapter = Db::getInstance();

        $sql = new Sql\Sql($dbAdapter);
        $delete = $sql->delete($table);

        if(!is_array($this->getValue()))
            $delete->where([$this->getIdentity()=>$this->getValue()]);

        else
            $delete->where(function(Sql\Where $where){
                $where->in($this->getIdentity(),$this->getValue());
            });

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

    public function deleteAll($table){

    }

}