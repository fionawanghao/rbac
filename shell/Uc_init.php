<?php
require_once "Mysql.php";
require_once "Redis.php";


class UcInit {
	
	private $db = null;
	
	private $redis = null;
	
	public function __construct() {
		$this->db = GetDb::getInstance()->getDb();
		$this->redis = GetRedis::getInstance()->getRedis();
	}
	
	private function getDomainIds(){
		$domain_ids = array();
		$sql = 'select id from uc_domain';
		$stm = $this->db->prepare($sql);
		if(!$stm->execute()){
			return $this->errorAjaxRender('fail to get domain_ids');
		}
		$domain_ids = $stm->fetchAll(PDO::FETCH_NUM);
		return $domain_ids;
	}
	
	private function getUserIds(){
		$user_ids = array();
		$sql = 'select id from uc_user';
		$stm = $this->db->prepare($sql);
		if(!$stm->execute()){
			return $this->errorAjaxRender('fail to get domain_ids');
		}
		$user_ids = $stm->fetchAll(PDO::FETCH_NUM);
		return $user_ids;
	}
	
	private function getAllRoleIds(){
		$role_ids = array();
		$sql = 'select id from uc_role';
		$stm = $this->db->prepare($sql);
		if(!$stm->execute()){
			return $this->errorAjaxRender('fail to get role_ids');
		}
		$role_ids = $stm->fetchAll(PDO::FETCH_NUM);
		return $role_ids;
	}
	
	private function initDomainAndUser() {
		$domain_ids = $this->getDomainIds();
		$user_ids = $this->getUserIds();
		$role_ids =array();
		$uc_roles_keys = array();
		foreach($domain_ids as $key => $val){
			foreach($val as $v){
				$domain_ids[$key] = $v;
			}
		}
		foreach($user_ids as $key => $val){
			foreach($val as $v){
				$user_ids[$key] = $v;
			}
		}
		
		foreach($domain_ids as $domain_id){
			foreach($user_ids as $user_id){
				$sql = 'select role_id from uc_user_domain_role_relation where domain_id=? and user_id=?';
				$stm = $this->db->prepare($sql);
				if(!$stm->execute(array($domain_id,$user_id))){
					return $this->errorAjaxRender('fail to get role_ids');
				}
				$role_ids = $stm->fetchAll(PDO::FETCH_NUM);
				
				if(!empty($role_ids) && is_array($role_ids)){
					foreach($role_ids as $key => $val){
						foreach($val as $v){
							$role_ids[$key] = $v;
						}
					}
					//将所有的role_ids放入redis
					$this->redis->set('uc_roles_'.$user_id.'_'.$domain_id, json_encode($role_ids));
					$uc_roles_keys[] ='uc_roles_'.$user_id.'_'.$domain_id;
				}
			}
		}
		//将存入redis的role_ids的key组成一个数组也存入到redis中，留用
		$this->redis->set('uc_roles_keys',json_encode($uc_roles_keys));
	}
	private function getAllResourceIdsOfRole($role_id){//$role_id 一维array
		$resource_ids = array();
		$sql = 'select resource_id from uc_domain_role_resource_relation where role_id=?';
		$stm = $this->db->prepare($sql);
		$stm->execute($role_id);
		$resource_ids = $stm->fetchAll(PDO::FETCH_NUM);
		if(!$resource_ids){
			$resource_ids = array();
		}
		foreach($resource_ids as $key => $val){
			foreach($val as $v){
				$resource_ids[$key] = $v;
			}
		}
		return $resource_ids;//一维数组
	}
	
	private function getAllResourceInfo($resource_ids){ //$reosurce_ids一维数组
		$num = count($resource_ids);
		$para = rtrim(str_repeat('?,',$num),',');
		$sql = 'select * from  uc_resource where id in ('.$para.')';
		$stm = $this->db->prepare($sql);
		$stm->execute($resource_ids);
		$resource_info = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $resource_info;
	}
	
	private function initRole() {
		$sql1 = 'select * from uc_role where id=?';
		$stm1 = $this->db->prepare($sql1);
		$role_ids = $this->getAllRoleIds();
		$kv = array();
		foreach($role_ids as $role_id){ // role_id = 28 27		
			$resource_info = array();
			$stm1->execute($role_id);	
			$role_info = $stm1->fetch(PDO::FETCH_ASSOC);
			$resource_ids = $this->getAllResourceIdsOfRole($role_id);
			if(empty($resource_ids)){
				$this->redis->set('uc_role_info_'.$role_id[0],json_encode(array($role_info)));
			}else{
				$resource_info = $this->getAllResourceInfo($resource_ids);
				$this->redis->set('uc_role_info_'.$role_id[0],json_encode(array($role_info,$resource_info)));
			}
		}
	}
	
	
	public function run() {
		$this->initDomainAndUser();
		$this->initRole();
	}
}

$init = new UcInit();
$init->run();

	
	
	

	
	
	
	
	
	
	
	


