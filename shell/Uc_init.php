<?php
	function jsonRender($data)
	{
        echo json_encode($data);
	}
	function getDb(){
		$db = null;
		if($db == null) {
			$database =  'domainpermission';
			$hostname = '192.168.10.172';
			$password = '123456';
			$username = 'wp';
			$port = '3306';
			$dsn = 'mysql:dbname='.$database.';host='.$hostname.';port='.$port;
			$db = new \PDO($dsn, $username, $password);	
		}
		return $db;
	}	
	
	function getRedis(){
		$redis = null;
		if($redis == null){
			$redis = new \Redis();
			$redis->connect('127.0.0.1',6379);
		}
		return $redis;
	}
	
	
	
	function ajaxRender($data = array(), $msg = null)
	{
		$data = array('ret' => 0, 'data' => $data, 'msg' => $msg);
		return jsonRender($data);
	}
	
	function errorAjaxRender($msg = null){
		$data = array('ret' => 1, 'msg' => $msg);
		return jsonRender($data);
	}
	
	function getDomainIds(){
		$domain_ids = array();
		$db = getDb();
		$sql = 'select id from uc_domain';
		$stm = $db->prepare($sql);
		if(!$stm->execute()){
			return errorAjaxRender('fail to get domain_ids');
		}
		$domain_ids = $stm->fetchAll(PDO::FETCH_NUM);
		return $domain_ids;
	}
	
	function getUserIds(){
		$user_ids = array();
		$db = getDb();
		$sql = 'select id from uc_user';
		$stm = $db->prepare($sql);
		if(!$stm->execute()){
			return errorAjaxRender('fail to get domain_ids');
		}
		$user_ids = $stm->fetchAll(PDO::FETCH_NUM);
		return $user_ids;
	}
	
	$redis = getRedis();
	$db = getDb();
	$domain_ids = getDomainIds();
	$user_ids = getUserIds();
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
			$stm = $db->prepare($sql);
			if(!$stm->execute(array($domain_id,$user_id))){
				return errorAjaxRender('fail to get role_ids');
			}
			$role_ids = $stm->fetchAll(PDO::FETCH_NUM);
			
			if(!empty($role_ids) && is_array($role_ids)){
				foreach($role_ids as $key => $val){
					foreach($val as $v){
						$role_ids[$key] = $v;
					}
				}
				//将所有的role_ids放入redis
				$redis->set('uc_roles_'.$user_id.'_'.$domain_id, json_encode($role_ids));
				$uc_roles_keys[] ='uc_roles_'.$user_id.'_'.$domain_id;
			}
			
		}
	}
	//将存入redis的role_ids的key组成一个数组也存入到redis中，留用
	$redis->set('uc_roles_keys',json_encode($uc_roles_keys));
	//var_dump($redis->get('uc_roles_23_7'));
	/*uc_role_info_roleId => array(
		name=>xxx
		desc=>xxxx,
		resourceinfo => array(
		array(
			'name' => ''
			id => ''
			resource_url => ''
		)
	)*/
	
	
	function getAllRoleIds(){
		$role_ids = array();
		$db = getDb();
		$sql = 'select id from uc_role';
		$stm = $db->prepare($sql);
		if(!$stm->execute()){
			return errorAjaxRender('fail to get role_ids');
		}
		$role_ids = $stm->fetchAll(PDO::FETCH_NUM);
		return $role_ids;
	}
	$all_role_ids = getAllRoleIds();
	$role_info = array();
	$a = array();
	foreach($all_role_ids as $v){
		 
			$sql1 = 'select * from uc_role where id=?';
			$sql2 = 'select * from  uc_domain_role_resource_relation where role_id=?';
			$stm1 = $db->prepare($sql1);
			if(!$stm1->execute($v)){
				return errorAjaxRender('fail to get role_info');
			}
			$role_info = $stm1->fetch(PDO::FETCH_ASSOC);
			$stm2 = $db->prepare($sql2);
			if(!$stm2->execute($v)){
				return errorAjaxRender('fail to get role_info');
			}
			$resource_info = $stm2->fetchAll(PDO::FETCH_ASSOC);
			if(is_array($resource_info) && !empty($resource_info)){
				$redis->set('uc_role_info_'.$v[0],json_encode(array($role_info,$resource_info)));
				$a[] = $resource_info;
			}
	}
	


