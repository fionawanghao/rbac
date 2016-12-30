<?php
use Service\Lib\ModelBase;

class DomainModel extends ModelBase
{
	public function insert($data)
	{
		$db = $this->getDb();
		$sql = "insert into ". UC_TABLE_UC_DOMAIN . " (domain_name,domain_desc,domain_type,domain_salt,default_role_id,status,create_time,update_time) 
		values (?,?,?,?,?,?,?,?)";
		if (!$db->prepare($sql)->execute($data)) {
			throw new \Exception('insert domain fail.');
		}
	}

	public function getList($condition = array())
	{
		$db = $this->getDb();
		if(isset($condition['istotal']) && $condition['istotal'] == true){
			
			$sql = "select count(*) from ".UC_TABLE_UC_DOMAIN . " where is_delete = 0";
			$stm = $db->prepare($sql);
			if (!$stm->execute()) {
				throw new \Exception('fetch items fail.');
			}
	
			$info = $stm->fetch(PDO::FETCH_ASSOC);
			$num = 0;
			
			if(is_array($info) && !empty($info)){
				$num = array_values($info)[0];
			}
			return $num;
		}
		
		if(isset($condition['start']) && isset($condition['limit'])){
			$sql = "select * from ".UC_TABLE_UC_DOMAIN . " where is_delete=0 limit ".$condition['start'].",".$condition['limit'];		
		}else{
			$sql = "select * from ".UC_TABLE_UC_DOMAIN . " where is_delete=0";
		}
		
		$stm = $db->prepare($sql);
		if(!$stm->execute()) {
			throw new \Exception('fetch items fail.');
		}
		$result = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $result;	
	}
	
	public function update($id,$data){
		$db = $this->getDb();
		$fields = array();
		foreach($data as $key => $val){
			$fields[] = $key. '=?';
		}
		$fields = implode(',',$fields);
		$data = array_values($data);
		$data[] = $id;
		$sql = "update ".UC_TABLE_UC_DOMAIN . " set ".$fields." where id=?";
		if (!$db->prepare($sql)->execute($data)) {
			throw new \Exception('update item fail.');
		}
	}
	public function domainBatchInfo($ids) {
		$num = count($ids);
		$para = rtrim(str_repeat('?,',$num),',');
		$db = $this->getDb();
		$sql = "select * from ".UC_TABLE_UC_DOMAIN ." where id in (" . $para . ")";
		$stm = $db->prepare($sql);
		if(!$stm->execute($ids)){
			throw new \Exception ('fail to get domainBatchInfo');
		}
		$info = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $info;
	}
	public function domainInfo($id)
	{
		$db = $this->getDb();
		$sql = "select * from ".UC_TABLE_UC_DOMAIN ." where id=?";
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($id))){
			throw new \Exception('fail to get domainInfo');
		}
		
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function domainInfoByName($domain_name)
	{
		$db = $this->getDb();
		$sql = "select * from ".UC_TABLE_UC_DOMAIN ." where domain_name=?";
		$stm = $db->prepare($sql);
		$ret = $stm->execute(array($domain_name));
		if(!$ret){
			return array();
		}
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function del($id)
	{
		$num = count($id);
		$para = rtrim(str_repeat('?,',$num),',');
		$db = $this->getDb();
		$sql = 'update '. UC_TABLE_UC_DOMAIN . ' set is_delete=1 where id in ('.$para.')';
		return $db->prepare($sql)->execute($id);
	}
}
