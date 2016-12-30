<?php
use Service\Lib\ModelBase;

class ResourceModel extends ModelBase
{
	public function add($data)
	{
		$db = $this->getDb();
		$sql = "insert into ". UC_TABLE_UC_RESOURCE . " (domain_id,resource_url,resource_name,resource_desc,status,create_time,update_time) 
		values (?,?,?,?,?,?,?)";
		if(!$db->prepare($sql)->execute($data)){
			throw new \Exception('fail to add');
		}
	}
	
	public function getList($condition = array())
	{
		$db = $this->getDb();
		if(isset($condition['is_total']) && $condition['is_total'] == true ){
			$sql = 'select count(*) from '.UC_TABLE_UC_RESOURCE .' where is_delete=0';
			$stm = $db->prepare($sql);
			$ret = $stm->execute();
			if(!$ret){
				return 0;
			}
			$num = 0;
			$result = $stm->fetch(PDO::FETCH_ASSOC);
			if(is_array($result) && !empty($result)){
				$num = array_values($result)[0];
			}
			return $num;	
		}
		
		if(isset($condition['start']) && isset($condition['limit'])){
			$sql = 'select * from '.UC_TABLE_UC_RESOURCE .' where is_delete=0 limit '.$condition['start'].','.$condition['limit'];
		}else{
			$sql = 'select * from '.UC_TABLE_UC_RESOURCE .' where is_delete=0';
		}
		
		$stm = $db->prepare($sql);
		$ret = $stm->execute();
		
		if(!$ret){
			return array();
		}
		
		$info = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function update($id,$data)
	{
		$db = $this->getDb();
		$fields = array();
		foreach($data as $key => $val){
			$fields[] = $key.'=?';
		}
		$fields = implode(',',$fields);
		$info = array_values($data);
		$info[] = $id;
		$sql = 'update '. UC_TABLE_UC_RESOURCE .' set '.$fields.' where id=?';
		if(!$db->prepare($sql)->execute($info)){
			throw new \Exception ('fail to update');
		}
	}
	public function resourceInfoOfDomain($domain_id,$resource_url){
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_RESOURCE .' where domain_id=? and resource_url=?';
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($domain_id,$resource_url))){
			throw new \Exception('fail to get resourceInfo');
		}
		$result = $stm->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
	
	public function roleBatchInfo($ids)
	{
		$db = $this->getDb();
		$num = count($ids);
		$para = rtrim(str_repeat('?,',$num),',');
		$sql = 'select * from '.UC_TABLE_UC_RESOURCE .' where id in ('.$para.')';
		$stm = $db->prepare($sql);
		if(!$stm->execute($ids)){
			throw new \Exception('fail to get roleBatchInfo');
		}
		$result = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
	public function resourceInfo($id)
	{
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_RESOURCE .' where id=?';
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($id))){
			throw new \Exception('fail to get resourceInfo');
		}
		
		$result = $stm->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
	
	public function resourceInfoByUrl($resource_url)
	{
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_RESOURCE .' where resource_url=?';
		$stm = $db->prepare($sql);
		$ret = $stm->execute(array($resource_url));
		if(!$ret){
			return array();
		}
		$result = $stm->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
	
	public function del($id)
	{
		$num = count($id);
		$para = rtrim(str_repeat('?,',$num),',');
		$db = $this->getDb();
		$sql = 'update '. UC_TABLE_UC_RESOURCE . ' set is_delete=1 where id in ('.$para.')';
		if(!$db->prepare($sql)->execute($id)){
			throw new \Exception('fail to delete');
		}
		
	}
}