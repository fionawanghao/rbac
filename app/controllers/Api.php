<?php 
use Service\Lib\Controller\Base;
class ApiController extends Base
{
	/*
	 
	domain_id uid
	
	key->value
	
	domian_id _ uid uc_role_1_2323232 > {}
	roles
	
	*/
	public function resourcesAction()
	{
		
		$api = new ApiModel;
		$user = new UserModel;
		$domain = new DomainModel;
		$grant = new GrantModel;
		$user_id = trim($this->getPost('user_id',''));
		$domain_id = trim($this->getPost('domain_id',''));
		$domain_info = $domain->domainInfo($domain_id);
		$user_info = $user->userInfo($user_id);
		$salt = $domain_info['domain_salt'];
		$token = trim($this->getPost('token',''));
		$public_key = $api->getPublicKey($token, $domain_id, $user_id,$salt);
		
		if(empty($token)){
			$error = '调用该接口必须填写token';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
	
		if(!$public_key){
			$error = 'token填写有误';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($user_id)){
			$error = '用户ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		$user_info = $user->userInfo($user_id);
		if(empty($user_info)){
			$error = '该用户不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['is_delete']) && ($user_info['is_delete'] == 1)){
			$error = '该用户已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['status']) && ($user_info['status'] == 2)){
			$error = '该用户状态为禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(empty($domain_id)){
			$error = '产品线ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		
		if(empty($domain_info)){
			$error = '该产品线不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($domain_info['is_delete']) && ($domain_info['is_delete'] == 1)){
			$error = '该产品线已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($domain_info['status']) && ($domain_info['status'] == 2)){
			$error = '该产品线状态为禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		/*$user_domain_info = $grant->userDomainInfo($user_id,$domain_id);
		if(empty($user_domain_info)){
			return $this->errorAjaxRender('该有用户在该产品线下没有权限');
		}*/
		
		$redis = $this->getRedis();
		$role_id = array();
		$resource_info = array();
		$result = array();
		if(!empty($redis->get('uc_roles_'.$user_id.'_'.$domain_id))){
			$role_id = json_decode($redis->get('uc_roles_'.$user_id.'_'.$domain_id));
			foreach($role_id as $v){
				$resource_info[] = json_decode($redis->get('uc_role_info_'.$v))[1];  
			}
			foreach($resource_info as $val){
					foreach($val as $v){
						$result[] = $v->resource_url;
					}
			}
			
		}else{
			try{
				$result = $api->resources($user_id,$domain_id);
				$redis->set('uc_roles_'.$user_id.'_'.$domain_id,json_encode($api->role_ids($user_id,$domain_id)));
			}catch(\Exception $e){
				$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return errorAjaxRender($e->getMessage());
			}
			
		}
		if(empty($result)){
			$error = '用户ID为'.$user_id.'的用户在产品线ID为'.$domain_id.'产品线下没有权限';
			$this->logger()->info($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}else{
			$info = '用户ID为'.$user_id.'的用户在产品线ID为'.$domain_id.'产品线下有如下权限';
			$this->logger()->info($info,$result);
			return $this->jsonRender($result);
		}
	
	
	}
	
	public function rolesAction()
	{
		$redis = $this->getRedis();
		$role_id = array();
		$api = new ApiModel;
		$user = new UserModel;
		$role = new RoleModel;
		$domain =  new DomainModel;
		$rolenames = array();
		$user_id = trim($this->getPost('user_id',''));
		$domain_id = trim($this->getPost('domain_id',''));
		$domain_info = $domain->domainInfo($domain_id);
		$salt = $domain_info['domain_salt'];
		$token = trim($this->getPost('token',''));
		$public_key = $api->getPublicKey($token, $domain_id, $user_id,$salt);
		if(empty($token)){
			$error = '调用该接口必须填写token';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
	
		if(!$public_key){
			$error = '请填写正确的token';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($user_id)){
			$error = '用户ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		$user_info = $user->userInfo($user_id);
		if(empty($user_info)){
			$error = '该用户不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['is_delete']) && ($user_info['is_delete'] == 1)){
			$error = '该用户已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['status']) && ($user_info['status'] == 2)){
			$error = '该用户状态为禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(empty($domain_id)){
			$error = '产品线ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		
		if(empty($domain_info)){
			$error = '该产品线不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($domain_info['is_delete']) && ($domain_info['is_delete'] == 1)){
			$error = '该产品线已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($domain_info['status']) && ($domain_info['status'] == 2)){
			$error = '该产品线状态为禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!empty($redis->get('uc_roles_'.$user_id.'_'.$domain_id))){
			$role_id = json_decode($redis->get('uc_roles_'.$user_id.'_'.$domain_id));
			foreach($role_id as $k => $v){
				$rolenames[$k]['role_id'] = json_decode($redis->get('uc_role_info_'.$v))[0]->id;  
				$rolenames[$k]['role_name'] = json_decode($redis->get('uc_role_info_'.$v))[0]->role_name;  
			}
		}else{
			try{
				$role_ids = $api->role_ids($user_id,$domain_id);
				$redis->set('uc_roles_'.$user_id.'_'.$domain_id,json_encode($role_ids));
			}catch(\Exception $e){
				$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return errorAjaxRender($e->getMessage());	
			}
		
			foreach($role_ids as $role_id){
				try{
					$result = $role->roleInfo($role_id);
				}catch(\Exception $e){
					$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
					return errorAjaxRender($e->getMessage());
				}	
				/*$rolenames[$role_id] = $result['role_name'];*/
				$rolenames[] = array(
					'role_id' => $role_id,
					'role_name' => $result['role_name']
				);
			}
		}
		if(empty($rolenames)){
			$error = '该用户在该产品线没有设置角色';
			$this->logger()->info($error,array('用户ID：'.$user_id,'产品线ID：'.$domain_id));
			return $this->errorAjaxRender($error);
		}
		$this->logger()->info('用户ID为'.$user_id.'的用户在产品线ID为'.$domain_id.'产品线下的角色如下',$rolenames);
		return $this->jsonRender($rolenames);
	}
	
	public function has_resourceAction()
	{
		$redis = $this->getRedis();
		$all_urls = array();
		$api = new ApiModel;
		$user = new UserModel;
		$domain =  new DomainModel;
		$resource = new ResourceModel;
		$user_id = trim($this->getPost('user_id',''));
		$domain_id = trim($this->getPost('domain_id',''));
		$resource_url = (trim($this->getPost('resource_url','')));
		$domain_info = $domain->domainInfo($domain_id);
		$salt = $domain_info['domain_salt'];
		$token = trim($this->getPost('token',''));
		$public_key = $api->getPublicKey($token, $domain_id, $user_id,$salt);
		$resource_infos = array();
		
		if(empty($token)){
			$error = '调用该接口必须填写token';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
	
		if(!$public_key){
			$error = '请填写正确的token';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($user_id)){
			$error = '用户ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		$user_info = $user->userInfo($user_id);
		if(empty($user_info)){
			$error = '该用户不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['is_delete']) && ($user_info['is_delete'] == 1)){
			$error = '该用户已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['status']) && ($user_info['status'] == 2)){
			$error = '该用户状态为禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error );
		}
		if(empty($domain_id)){
			$error = '产品线ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		
		if(empty($domain_info)){
			$error = '该产品线不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($domain_info['is_delete']) && ($domain_info['is_delete'] == 1)){
			$error = '该产品线已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($domain_info['status']) && ($domain_info['status'] == 2)){
			$error = '该产品线状态为禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender('该产品线状态为禁用');
		}
		if(empty($resource_url)){
			$error = '权限不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		
		$resource_info = $resource->resourceInfoByUrl($resource_url);
		if(empty($resource_info)){
			$error = '该权限不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($resource_info['is_delete']) && ($resource_info['is_delete'] == 1)){
			$error = '该权限已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($resource_info['status']) && ($resource_info['status'] == 2)){
			$error = '该权限状态为禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!empty($redis->get('uc_roles_'.$user_id.'_'.$domain_id))){
			$role_id = json_decode($redis->get('uc_roles_'.$user_id.'_'.$domain_id));
			foreach($role_id as $v){
				$resource_infos[] = json_decode($redis->get('uc_role_info_'.$v))[1];  
			}
			
			foreach($resource_infos as $val){
					foreach($val as $v){
						$all_urls[]['resource_url'] = $v->resource_url;
					}
			}
			
		}else{
			try{
				$all_urls = $api->resources($user_id,$domain_id);
				$redis->set('uc_roles_'.$user_id.'_'.$domain_id,$api->role_ids($user_id,$domain_id));
			}catch(\Exception $e){
				$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($e->getMessage());
			}	
		}
		$mark = false;
		foreach($all_urls as $val){
			if($resource_url == $val['resource_url']){
				$mark = true;
			}	
		}
		
		if(!$mark){
			$info = '该用户没有该权限';
			$this->logger()->info($info,array('用户ID：'.$user_id,'产品线ID：'.$domain_id));
			return $this->errorAjaxRender($info);
		}else{
			$info = '该用户有该权限';
			$this->logger()->info($info,array('用户ID：'.$user_id,'产品线ID：'.$domain_id,'权限：'.$resource_url));
			return $this->ajaxRender(array(),$info);
		}		
	}
	
}