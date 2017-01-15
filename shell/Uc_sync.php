<?php 
require_once "Mysql.php";
require_once "Redis.php";

class UcSync {
	private $db = null;
	private $redis = null;
	public function __construct(){
		$this->redis = GetRedis::getInstance()->getRedis();
		$this->db = GetDb::getInstance()->getDb();
	}
	
	private function syncDomain($message) {
		switch($message['opt']) {
			case 'add':
				break;
			case 'update':
				break;
			case 'delete':
				$this->syncDomainDelete($message);
				break;		
		}
	}
	
	private function syncRole($message){
		switch($message['opt']) {
			case 'add':
				$this->syncRoleAdd($message);
				break;
			case 'update':
				$this->syncRoleUpdate($message);
				break;
			case 'delete':
				$this->syncRoleDelete($message);
				break;
		}
	}
	
	private function syncUser($message){
		switch($message['opt']) {
			case 'add':
				break;
			case 'update':
				break;
			case 'delete':
				$this->syncUserDelete($message);
				break;
		}
	}
	
	private function syncResource($message){
		switch($message['opt']) {
			case 'add':
				break;
			case 'update':
				break;
			case 'delete':
				$this->syncResourceDelete($message);
				break;
		}
	}
	
	private function syncGrant($message){
		switch($message['opt']){
			case 'add_role':
				$this->syncGrantAddRole($message);
				break;
			case 'del_role':
				$this->syncGrantDelRole($message);
				break;
			case 'add_resource':
				$this->syncGrantAddResource($message);
				break;
			case 'del_resource':
				$this->syncGrantDelResource($message);
				break;
		}
	}
	
	private function syncDomainDelete($message){
		$uc_roles_keys = json_decode($this->redis->get('uc_roles_keys'),true);
		$domain_id = $message['data']['domain_id'];
			foreach($domain_id as $id){
				foreach($uc_roles_keys as $k => $key){
					$arr = explode('_',$key);
					if($id == $arr[3]){
						$this->redis->del($key);
						unset($uc_roles_keys[$k]);
					}else{
						continue;
					}
				}
			}
	
			$this->redis->del('uc_roles_keys');
			$this->redis->set('uc_roles_keys',json_encode($uc_roles_keys));
	}
	
	private function syncRoleAdd($message){
		$role_id = $message['data']['role_id'];
		$role_info = $message['data']['role_info'];
		$this->redis->set('uc_role_info_'.$role_id,json_encode(array($role_info)));
		
	}
	
	private function syncRoleUpdate($message){
		$role_id = $message['data']['role_id'];
		$role_info = $message['data']['role_info'];
		$this->redis->set('uc_role_info_'.$role_id,json_encode(array($role_info)));
	}
	
	private function syncRoleDelete($message){
		$role_id = $message['data']['role_id'];
		$keys = array();
		foreach($role_id as $id){
			$this->redis->del('uc_role_info_'.$id);	
		}
	}
	
	private function syncUserDelete($message){
		$user_id = $message['data']['user_id'];
		$uc_roles_keys = json_decode($this->redis->get('uc_roles_keys'),true);
		foreach($user_id as $id){
			foreach($uc_roles_keys as $k => $key){
				$arr = explode('_',$key);
				if($id == $arr[2]){
					$this->redis->del($key);
					unset($uc_roles_keys[$k]);
				}
			}
		}
	
		$this->redis->del('uc_roles_keys');
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
	
	private function syncResourceDelete($message){  
		$resource_id = $message['data']['resource_id'];
		$num = count($resource_id);
		$para = rtrim(str_repeat('?,',$num),',');
		$sql = 'select role_id from uc_domain_role_resource_relation where resource_id in ('.$para.')';
		$stm = $this->db->prepare($sql);
		$stm->execute($resource_id);
		$role_ids = $stm->fetchAll(PDO::FETCH_NUM);
		foreach($role_ids as $role_id){ // role_id = 28 27		
			$sql1 = 'select * from uc_role where id = ?';
			$stm1 = $this->db->prepare($sql1);
			$stm1->execute($role_id);	
			$role_info = $stm1->fetch(PDO::FETCH_ASSOC);
			$resource_ids = $this->getAllResourceIdsOfRole($role_id);
			$mark = false;
			if(!empty($resource_ids)){
				foreach($resource_ids as $key => $res_id){
					foreach($resource_id as $v)
					if($res_id == $v){
						unset($resource_ids[$key]);
					}else{
						continue;
					}
				}
				if(!empty($resource_ids)){
					$mark = true;
				}
			}
			if(!$mark){
				$this->redis->set('uc_role_info_'.$role_id[0],json_encode(array($role_info)));
			}else{
				$resource_info = $this->getAllResourceInfo(array_values($resource_ids));
				$this->redis->set('uc_role_info_'.$role_id[0],json_encode(array($role_info,$resource_info)));
			}
		}
	}
	
	private function syncGrantAddRole($message){
		$user_id = $message['data']['user_id'];
		$role_id = $message['data']['role_id'];
		$domain_id = $message['data']['domain_id'];
		//var_dump($this->redis->get('uc_roles_'.$user_id.'_'.$domain_id));
		//var_dump($this->redis->get('uc_roles_keys'));
		$values = json_decode($this->redis->get('uc_roles_'.$user_id.'_'.$domain_id),true);
		if(empty($values)){
			$this->redis->set('uc_roles_'.$user_id.'_'.$domain_id,json_encode(array($role_id)));
		}else{
			$values[] = $role_id;
			$this->redis->set('uc_roles_'.$user_id.'_'.$domain_id,json_encode($values));
			$uc_roles_keys = json_decode($this->redis->get('uc_roles_keys'),true);
			$mark = false;
			foreach($uc_roles_keys as $v){
				if($v == 'uc_roles_'.$user_id.'_'.$domain_id){
					$mark = true;
				}
			
			if(!$mark){
				$uc_roles_keys[] = 'uc_roles_'.$user_id.'_'.$domain_id;
				$this->redis->set('uc_roles_keys',json_encode($uc_roles_keys));
			}
		}
		//var_dump($this->redis->get('uc_roles_'.$user_id.'_'.$domain_id));
		//var_dump($this->redis->get('uc_roles_keys'));
	}
	
	private function syncGrantDelRole($message){
		$uc_roles_keys = json_decode($this->redis->get('uc_roles_keys'),true);
		$user_id = $message['data']['user_id'];
		$role_id = $message['data']['role_id'];
		$role_info = json_decode($this->redis->get('uc_role_info_'.$role_id),true);
		$domain_id = $role_info[0]['domain_id'];
		$roles = json_decode($this->redis->get('uc_roles_'.$user_id.'_'.$domain_id),true);
		
		foreach($roles as $key => $role){
			if($role_id == $role){
				unset($roles[$key]);
				break;
			}
		}
		$this->redis->set('uc_roles_'.$user_id.'_'.$domain_id,json_encode($roles));
		$newroles = json_decode($this->redis->get('uc_roles_'.$user_id.'_'.$domain_id),true);
		if(empty($newroles)){
			$this->redis->del('uc_roles_'.$user_id.'_'.$domain_id);
			foreach($uc_roles_keys as $key => $v){
				if($v == 'uc_roles_'.$user_id.'_'.$domain_id){
					unset($uc_roles_keys[$key]);
				}
			}
			$this->redis->set('uc_roles_keys',json_encode($uc_roles_keys));
		}	
	}	
	
	private function syncGrantAddResource($message){
		$role_id = $message['data']['role_id'];
		$resource_id = $message['data']['resource_id'];
		$uc_role = json_decode($this->redis->get('uc_role_info_'.$role_id),true);
		$sql = 'select * from uc_resource where id = ?';
		$stm = $this->db->prepare($sql);
		$stm->execute(array($resource_id));
		$resource_info = $stm->fetch(PDO::FETCH_ASSOC);
		$uc_role[1][] = $resource_info;
		$this->redis->set('uc_role_info_'.$role_id,json_encode($uc_role));
	}
	
	private function syncGrantDelResource($message){
		$role_id = $message['data']['role_id'];
		$resource_id = $message['data']['resource_id'];
		$uc_role = json_decode($this->redis->get('uc_role_info_'.$role_id),true);
		$resources = $uc_role[1];
		$role = $uc_role[0]; 
		$num = count($resources);
		if($num == 1 && $resources[0]['id'] == $resource_id){
			$this->redis->set('uc_role_info_'.$role_id,json_encode(array($role)));
			return false;
		}
		foreach($resources as $key => $res){
			if($res['id'] == $resource_id){
				unset($resources[$key]);
			}
		}
		$new_uc_role = json_encode(array($uc_role[0],$resources));
		$this->redis->set('uc_role_info_'.$role_id,$new_uc_role);
	}
	
	public function run() {
		$message = json_decode($this->redis->lpop('uc_sync_queue'),true);
		//var_dump($this->redis->mget(array('uc_role_info_29','uc_role_info_30')));
		switch ($message['type']) {
			case 'domain':
				$this->syncDomain($message);
				break;
			case 'role':
				$this->syncRole($message);
				break;
			case 'user':
				$this->syncUser($message);
				break;
			case 'resource':
				$this->syncResource($message);
				break;
			case 'grant':
				$this->syncGrant($message);
				break;
			
		}
	}
}

$sync = new UcSync();
$sync->run();

	
	
	// 批量获取数据
	//var_dump($redis->mGet(array('uc_role_info_18', 'uc_role_info_19')));
	
	
	/*
	
	
	function syncRole($message) {
		
	}
	
	*/
	
	
	
	/*elseif($message['type'] == 'resource'){
		if($message['opt'] == 'add'){
			$resource_id = $message['data']['resource_id'];
			$resource_info = $message['data']['resource_info'];
			$redis->set('uc_role_info_'.$role_id,json_encode(array($resource_info)));
		}elseif($message['opt'] == 'update'){
			$resource_id = $message['data']['resource_id'];
			$resource_info = $message['data']['resource_info'];
			$redis->set('uc_role_info_'.$role_id,json_encode(array($resource_info)));
		}elseif($message['opt'] == 'delete'){
			$resource_id = $message['data']['resource_id'];
			$keys = array();
			var_dump(1111);
			foreach($resource_id as $id){
				var_dump($redis->get('uc_resource_info_'.$id));
				$redis->del('uc_role_info_'.$id);
				var_dump($redis->get('uc_resource_info_'.$id));
			}	
		}
	}*/
	

	
	/*$message = array(
			'type' => 'domain',
			'opt' => 'delete',
			'data' => array(
			'domain_id' => $id,
			)
		);*/
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	