<?php
use Service\Lib\ModelBase;

class ApiModel extends ModelBase
{
	//如果想控制对某一个产品线的查看权限可以将domain salt 加入到key里面
	public function getPublicKey($user_id,$salt){
		$public_key = md5($user_id.'9'.$salt);
		return $public_key;
	}
	
	//封装一个方法可以查询某个用户在某个产品线下的所有权限
	public function resources($user_id,$domain_id)
	{
		$db = $this->getDb();
		$resource_url = array();
		$role_ids = $this->role_ids($user_id,$domain_id);
		$sql = 'select * from '.UC_TABLE_UC_DOMAIN_ROLE_RESOURCE_RELATION .' where domain_id=? and role_id=?';
		
		foreach($role_ids as $role_id){
			$stm = $db->prepare($sql);
			if(!$stm->execute(array($domain_id,$role_id))){
				throw new \Exception('fail to get resource information');
			}
			
			$info = array($stm->fetchAll(PDO::FETCH_ASSOC));
			foreach($info as $val){
				foreach($val as  $v){
					//$resource_url['resource_id'.$v['resource_id']] = $v['resource_url'];
					$resource_url[] = array(
						'resource_id' => $v['resource_id'],
						'resource_url' => $v['resource_url']
					);
				}	
			}
		}
	
		return $resource_url;
	}
	//封装一个方法可以查询某个用户在某个产品线下的所有角色
	public function role_ids($user_id,$domain_id)
	{
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_UC_USER_DOMAIN_ROLE_RELATION .' where user_id=? and domain_id=?';
		$stm = $db->prepare($sql);
		
		$role_ids =  array();
		if(!$stm->execute(array($user_id,$domain_id))){
			throw new \Exception('fail to get role ids');
		}
		$info = $stm->fetchAll(PDO::FETCH_ASSOC);
		foreach($info as $val){
			$role_ids[] = $val['role_id'];
		}
		return $role_ids;
	}
}