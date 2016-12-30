<?php 
use Service\Lib\ModelBase;

class RoleModel extends ModelBase
{
	public function insert($data){
		$db = $this->getDb();
		$sql = 'insert into '.UC_TABLE_UC_ROLE .' (domain_id,role_name,role_desc,role_type,status,create_time,update_time) values (?,?,?,?,?,?,?)';
		if(!$db->prepare($sql)->execute($data)){
			throw new \Exception('fail to insert');
		}
	}

	public function getList($condition = array())
	{
		$db = $this->getDb();
		if(isset($condition['is_total']) && $condition['is_total'] == true){
			$sql = 'select count(*) from '.UC_TABLE_UC_ROLE .' where is_delete=0';
			$stm = $db->prepare($sql);
			$ret = $stm->execute();
			if(!$ret){
				return 0;
			}
			$result = $stm->fetch(PDO::FETCH_ASSOC);
			$total = 0;
			if(is_array($result) && !empty($result)){
				$total = array_values($result)[0];
			}
			return $total;
		}
		
		if(isset($condition['start']) && isset($condition['limit'])){
			$sql = 'select * from '.UC_TABLE_UC_ROLE .' where is_delete=0 limit '.$condition['start'].','.$condition['limit'];
		}else{
			$sql = 'select * from '.UC_TABLE_UC_ROLE .' where is_delete=0';
		}
		$stm = $db->prepare($sql);
		$ret = $stm->execute();
		if(!$ret){
			return array();
		}
		$info = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function roleInfoByNameDomainId($role_name,$domain_id){
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_ROLE .' where role_name=? and domain_id=?';
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($role_name,$domain_id))){
			throw new \Exception('fail to get roleInfo');
		}
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function roleInfo($id){
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_ROLE .' where id=?';
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($id))){
			throw new \Exception('fail to get roleInfo');
		}
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function roleBatchInfo($ids){
		$db = $this->getDb();
		$num = count($ids);
		$para = rtrim(str_repeat('?,',$num),',');
		$sql = 'select * from '.UC_TABLE_UC_ROLE .' where id in ('.$para.')';
		
		$stm = $db->prepare($sql);
		if(!$stm->execute($ids)){
			throw new \Exception('fail to get roleBatchInfo');
		}
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function rolesOfOneDomain($domain_id){
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_ROLE .' where domain_id=?';
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($domain_id))){
			throw new \Exception('fail to get roles');
		}
		$info = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $info;
	}
	
	public function update($id,$data){
		$db = $this->getDb();
		$fields = array();
		foreach($data as $key => $val){
			$fields[] = $key. '=?';
		}
		$fields = implode(',',$fields);
		$info = array_values($data);
		$info[] = $id;
		$sql = "update ".UC_TABLE_UC_ROLE . " set ".$fields." where id=?";
		if(!$db->prepare($sql)->execute($info)){
			throw new \Exception('fail to update');
		}
		
	}
	
	public function del($id)
	{
		$num = count($id);
		$para = rtrim(str_repeat('?,',$num),',');
		$db = $this->getDb();
		$sql = 'update '. UC_TABLE_UC_ROLE . ' set is_delete=1 where id in ('.$para.')';
		if(!$db->prepare($sql)->execute($id)){
			throw new \Exception('fail to delete');
		}
		
	}
}