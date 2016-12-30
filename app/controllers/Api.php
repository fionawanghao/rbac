<?php 
use Service\Lib\Controller\Base;
class ApiController extends Base
{
	public function resourcesAction()
	{
		$api = new ApiModel;
		$user = new UserModel;
		$domain = new DomainModel;
		$grant = new GrantModel;
		$token = trim($this->getPost('token',''));
		$user_id = trim($this->getPost('user_id',''));
		$domain_id = trim($this->getPost('domain_id',''));
		$domain_info = $domain->domainInfo($domain_id);
		$user_info = $user->userInfo($user_id);
		$salt = $domain_info['domain_salt'];
		$public_key = $api->GetPublicKey($user_id,$salt);
		//var_dump($public_key);
		if(empty($token)){
			return $this->errorAjaxRender('调用该接口必须填写token');
		}
	
		if($token != $public_key){
			return $this->errorAjaxRender('请填写正确的token');
		}
		
		if(empty($user_id)){
			return $this->errorAjaxRender('用户ID不能为空');	
		}
		$user_info = $user->userInfo($user_id);
		if(empty($user_info)){
			return $this->errorAjaxRender('该用户不存在');
		}
		
		if(isset($user_info['is_delete']) && ($user_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该用户已经删除');
		}
		
		if(isset($user_info['status']) && ($user_info['status'] == 2)){
			return $this->errorAjaxRender('该用户状态为禁用');
		}
		if(empty($domain_id)){
			return $this->errorAjaxRender('产品线ID不能为空');	
		}
		
		if(empty($domain_info)){
			return $this->errorAjaxRender('该产品线不存在');
		}
		
		if(isset($domain_info['is_delete']) && ($domain_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该产品线已经删除');
		}
		
		if(isset($domain_info['status']) && ($domain_info['status'] == 2)){
			return $this->errorAjaxRender('该产品线状态为禁用');
		}
		
		/*$user_domain_info = $grant->userDomainInfo($user_id,$domain_id);
		if(empty($user_domain_info)){
			return $this->errorAjaxRender('该有用户在该产品线下没有权限');
		}*/
		
		try{
			$result = $api->resources($user_id,$domain_id);
		}catch(\Exception $e){
			return errorAjaxRender($e->getMessage());
		}
		if(empty($result)){
			return $this->errorAjaxRender('该有用户在该产品线下没有权限');
		}else{
			return $this->jsonRender($result);
		} 
	}
	
	public function rolesAction()
	{
		$api = new ApiModel;
		$user = new UserModel;
		$role = new RoleModel;
		$domain =  new DomainModel;
		$rolenames = array();
		$user_id = trim($this->getPost('user_id',''));
		$domain_id = trim($this->getPost('domain_id',''));
		$domain_info = $domain->domainInfo($domain_id);
		$token = trim($this->getPost('token',''));
		$salt = $domain_info['domain_salt'];
		$public_key = $api->GetPublicKey($user_id,$salt);
		if(empty($token)){
			return $this->errorAjaxRender('调用该接口必须填写token');
		}
	
		if($token != $public_key){
			return $this->errorAjaxRender('请填写正确的token');
		}
		
		if(empty($user_id)){
			return $this->errorAjaxRender('用户ID不能为空');	
		}
		$user_info = $user->userInfo($user_id);
		if(empty($user_info)){
			return $this->errorAjaxRender('该用户不存在');
		}
		
		if(isset($user_info['is_delete']) && ($user_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该用户已经删除');
		}
		
		if(isset($user_info['status']) && ($user_info['status'] == 2)){
			return $this->errorAjaxRender('该用户状态为禁用');
		}
		if(empty($domain_id)){
			return $this->errorAjaxRender('产品线ID不能为空');	
		}
		
		if(empty($domain_info)){
			return $this->errorAjaxRender('该产品线不存在');
		}
		
		if(isset($domain_info['is_delete']) && ($domain_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该产品线已经删除');
		}
		
		if(isset($domain_info['status']) && ($domain_info['status'] == 2)){
			return $this->errorAjaxRender('该产品线状态为禁用');
		}
		
		try{
			$role_ids = $api->role_ids($user_id,$domain_id);
		}catch(\Exception $e){
			return errorAjaxRender($e->getMessage());	
		}
		
		foreach($role_ids as $role_id){
			try{
				$result = $role->roleInfo($role_id);
			}catch(\Exception $e){
				return errorAjaxRender($e->getMessage());
			}	
			/*$rolenames[$role_id] = $result['role_name'];*/
			$rolenames[] = array(
				'role_id' => $role_id,
				'role_name' => $result['role_name']
			);
			 
		}
		if(empty($rolenames)){
			return $this->errorAjaxRender('该用户在该产品线没有设置角色');
		}
		return $this->jsonRender($rolenames);
	}
	
	public function has_resourceAction()
	{
		$api = new ApiModel;
		$user = new UserModel;
		$domain =  new DomainModel;
		$resource = new ResourceModel;
		$user_id = trim($this->getPost('user_id',''));
		$domain_id = trim($this->getPost('domain_id',''));
		$resource_url = (trim($this->getPost('resource_url','')));
		$token = trim($this->getPost('token',''));
		$domain_info = $domain->domainInfo($domain_id);
		$salt = $domain_info['domain_salt'];
		$public_key = $api->GetPublicKey($user_id,$salt);
		
		if(empty($token)){
			return $this->errorAjaxRender('调用该接口必须填写token');
		}
	
		if($token != $public_key){
			return $this->errorAjaxRender('请填写正确的token');
		}
		
		if(empty($user_id)){
			return $this->errorAjaxRender('用户ID不能为空');	
		}
		$user_info = $user->userInfo($user_id);
		if(empty($user_info)){
			return $this->errorAjaxRender('该用户不存在');
		}
		
		if(isset($user_info['is_delete']) && ($user_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该用户已经删除');
		}
		
		if(isset($user_info['status']) && ($user_info['status'] == 2)){
			return $this->errorAjaxRender('该用户状态为禁用');
		}
		if(empty($domain_id)){
			return $this->errorAjaxRender('产品线ID不能为空');	
		}
		
		if(empty($domain_info)){
			return $this->errorAjaxRender('该产品线不存在');
		}
		
		if(isset($domain_info['is_delete']) && ($domain_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该产品线已经删除');
		}
		
		if(isset($domain_info['status']) && ($domain_info['status'] == 2)){
			return $this->errorAjaxRender('该产品线状态为禁用');
		}
		if(empty($resource_url)){
			return $this->errorAjaxRender('权限不能为空');	
		}
		
		$resource_info = $resource->resourceInfoByUrl($resource_url);
		if(empty($resource_info)){
			return $this->errorAjaxRender('该权限不存在');
		}
		
		if(isset($resource_info['is_delete']) && ($resource_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该权限已经删除');
		}
		
		if(isset($resource_info['status']) && ($resource_info['status'] == 2)){
			return $this->errorAjaxRender('该权限状态为禁用');
		}
		try{
			$all_urls = $api->resources($user_id,$domain_id);
		}catch(\Exception $e){
				return $this->errorAjaxRender($e->getMessage());
		}	
		
		$mark = false;
		foreach($all_urls as $val){
			if($resource_url == $val['resource_url']){
				$mark = true;
			}	
		}
		
		if(!$mark){
			return $this->errorAjaxRender('该用户没有该权限');
		}else{
			return $this->ajaxRender(array(),'该用户有该权限');
		}	
		
		
			
		
	}
	
}