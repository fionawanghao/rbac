<?php
use Service\Lib\ModelBase;

class GrantModel extends ModelBase
{
	public function add_role($data)
	{
		$db = $this->getDb();
		$sql = 'insert into '.UC_TABLE_UC_USER_DOMAIN_ROLE_RELATION .' (user_id,domain_id,role_id) values (?,?,?)';
		return $db->prepare($sql)->execute($data);
		
	}
	
	public function roleUserInfo($user_id,$role_id){
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_USER_DOMAIN_ROLE_RELATION .' where user_id=? and role_id=?';
		$stm = $db->prepare($sql);
		$ret = $stm->execute(array($user_id,$role_id));
		if(!$ret){
			return array();
		}
		$result = $stm->fetch(PDO::FETCH_ASSOC);
		return $result;
		
	}
	
	public function userDomainInfo($user_id,$domain_id){
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_USER_DOMAIN_ROLE_RELATION .' where user_id=? and domain_id=?';
		$stm = $db->prepare($sql);
		$ret = $stm->execute(array($user_id,$domain_id));
		if(!$ret){
			return array();
		}
		$result = $stm->fetch(PDO::FETCH_ASSOC);
		return $result;
		
	}
	
	public function del_role($user_id,$role_id){
		$db = $this->getDb();
		$sql = 'delete from '.UC_TABLE_UC_USER_DOMAIN_ROLE_RELATION .' where user_id=? and role_id=?';
		return $db->prepare($sql)->execute(array($user_id,$role_id));
	}
	
	public function add_resource($data)
	{
		$db = $this->getDb();
		$sql = 'insert into '.UC_TABLE_UC_DOMAIN_ROLE_RESOURCE_RELATION .' (domain_id,role_id,resource_id,resource_url) values (?,?,?,?)';
		return $db->prepare($sql)->execute($data);
	}
	
	public function roleResourceInfo($role_id,$resource_id)
	{
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_DOMAIN_ROLE_RESOURCE_RELATION .' where role_id=? and resource_id=?';
		$stm = $db->prepare($sql);
		$ret = $stm->execute(array($role_id,$resource_id));
	
		if(!$ret){
			return array();
		}
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		if(is_array($info)){
			return $info;
		}else{
			return array();
		}
		
	}
	
	public function del_resource($role_id,$resource_id)
	{
		$db = $this->getDb();
		$sql = 'delete from '.UC_TABLE_UC_DOMAIN_ROLE_RESOURCE_RELATION .' where role_id=? and resource_id=?';
		return $db->prepare($sql)->execute(array($role_id,$resource_id));
	}
}