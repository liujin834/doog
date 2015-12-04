<?php
namespace Sookon\Db;

use Sookon\Helpers\Assist;

class Dbh
{
	private $db;				//传入PDO对象.
	private $product = 0;		//产品环境
	
	function __construct($db = NULL)
	{
        if($db == NULL)
		    $this->db = Pdo::getInstance();
        else
            $this->db = $db;
	}
	
	function insert($table,$data,$return=false)
	{
		$fields = array();
		$datas = array();
		
		foreach($data as $k=>$v)
		{
			$fields[] = '"'.$k.'"';
			if(is_int($v) || is_float($v) || is_bool($v) || $v === "NULL")
			{
				$datas[] = $v;
			}else{
				if(preg_match("/\'/",$v))
				{
					$v = preg_replace("/\'/","''",$v);
				}
				$datas[] = "'".$v."'";
			}
		}
		
		$fields = join(",",$fields);
		$datas = join(",",$datas);
			
		if($return == false){
			
			$sql = "INSERT INTO \"".$table."\" ($fields) VALUES ($datas)";
			try{
				return $this->db->exec($sql);
			}catch (\Exception $e) {
				if($this->product)
				{
					return false;
				}else{
					echo 'Caught exception: '.  $e->getMessage(). "\n";
				}
			}
		}else{
			$sql = "INSERT INTO \"".$table."\" ($fields) VALUES ($datas) RETURNING id";
			//exit($sql);
			try{
				$sth = $this->db->prepare($sql);
				if($sth->execute())
				{
					$temp = $sth->fetch(\PDO::FETCH_ASSOC);
					return $temp['id'];
				}else{
					return false;
				}
			}catch (\Exception $e) {
				if($this->product)
				{
					return false;
				}else{
					echo Assist::Dump('Caught exception: '.  $e->getMessage(). "\n"."SQL:".$sql,false);
				}
			}
		}
	}//insert
	
	function update($table,$data,$condition="",$return=false)
	{
		$ups = array();
		
		foreach($data as $k=>$v)
		{
			if(is_int($v) || is_float($v) || is_bool($v) || $v === "NULL")
			{
				$ups[] = '"'.$k.'"='.$v;
			}else{
				if(preg_match("/\'/",$v))
				{
					$v = preg_replace("/\'/","''",$v);
				}
				$ups[] = '"'.$k.'"=\''.$v."'";
			}
		}
		
		$fields = join(",",$ups);
		
		if(!empty($condition))
		{
			$wheresql = " WHERE ".$condition;
		}else{
			$wheresql = "";
		}
		
		if($return == false){
			
			try{
				$sql = "UPDATE \"".$table."\" SET $fields $wheresql";
				if($this->db->exec($sql))
				{
					return true;
				}else{
					return false;
				}
			}catch (\Exception $e) {
				if($this->product)
				{
					return false;
				}else{
					echo 'Caught exception: '.  $e->getMessage(). "\n";
				}
			}
		}else{
            $sql = "UPDATE \"".$table."\" SET $fields $wheresql RETURNING id";
            //exit($sql);
            try{
                $sth = $this->db->prepare($sql);
                if($sth->execute())
                {
                    $temp = $sth->fetch(\PDO::FETCH_ASSOC);
                    return $temp['id'];
                }else{
                    return false;
                }
            }catch (\Exception $e) {
                if($this->product)
                {
                    return false;
                }else{
                    echo Assist::Dump('Caught exception: '.  $e->getMessage(). "\n"."SQL:".$sql,false);
                }
            }
		}

	}//update
	
}