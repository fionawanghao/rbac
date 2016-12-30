<?php
use Service\Lib\ModelBase;

class UserModel extends ModelBase
{
	public function add($data){
		$db = $this->getDb();
		$sql = 'insert into '. UC_TABLE_UC_USER .' (network_type,name,image,email,status) values (?,?,?,?,?)';
		if(!$db->prepare($sql)->execute($data)){
			throw new \Exception('fail to insert');
		}
	}
	
	public function getList($condition = array())
	{
		$db = $this->getDb();
		if(isset($condition['istotal']) && $condition['istotal'] == true){
			$sql = "select count(*) from ".UC_TABLE_UC_USER . " where is_delete = 0";
			$stm = $db->prepare($sql);
			if(!$stm->execute()){
				throw new \Exception('fail to get total');
			}
	
			$info = $stm->fetch(PDO::FETCH_ASSOC);
			$num = 0;
			
			if(is_array($info) && !empty($info)){
				$num = array_values($info)[0];
			}
			return $num;
		}
		
		if(isset($condition['start']) && isset($condition['limit'])){
			$sql = "select * from ".UC_TABLE_UC_USER . " where is_delete = 0 limit ".$condition['start'].",".$condition['limit'];	
		}else{
			$sql = "select * from ".UC_TABLE_UC_USER. " where is_delete = 0";
		}
			$stm = $db->prepare($sql);
			if(!$stm->execute()){
				throw new \Exception('fail to get list result');
			}
			$result = $stm->fetchAll(PDO::FETCH_ASSOC);
			return $result;	
	}	
	
	public function UserInfoByName($name){
		$db = $this->getDb();
		$sql = "select * from ".UC_TABLE_UC_USER ." where name=?";
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($name))){
			throw new \Exception('fail to get UserInfoByName');
		}
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function userBatchInfo($ids)
	{
		$db = $this->getDb();
		$num = count($ids);
		$para = rtrim(str_repeat('?,',$num),',');
		$sql = 'select * from '.UC_TABLE_UC_USER .' where id in ('.$para.')';
		$stm = $db->prepare($sql);
		if(!$stm->execute($ids)){
			throw new \Exception('fail to get userInfo');
		}	
		$info = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $info;
	}
	public function userInfo($id)
	{
		$db = $this->getDb();
		$sql = "select * from ".UC_TABLE_UC_USER ." where id=?";
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($id))){
			throw new \Exception('fail to get userInfo');
		}
		
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function del($id)
	{
		$db = $this->getDb();
		$num = count($id);
		$para = rtrim(str_repeat('?,',$num),',');		
		$sql = 'update '. UC_TABLE_UC_USER . ' set is_delete=1 where id in ('.$para.')';
		if(!$db->prepare($sql)->execute($id)){
			throw new \Exception('fail to del');
		}
	}
	
	public function update($id,$data){
		$db = $this->getDb();
		$fields = array();
		foreach($data as $key=>$val){
			$fields[] = $key.'=?';
		}
		$fields = implode(',',$fields);
		$info = array_values($data);
		$info[] = $id;
		$sql = 'update '.UC_TABLE_UC_USER .' set '. $fields.'  where id=?';
		if(!$db->prepare($sql)->execute($info)){
			throw new \Exception('fail to update');
		}
	}
			
			
			
			
			
			
			
}