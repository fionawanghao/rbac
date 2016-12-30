<?php 
use Service\Lib\Controller\Base;
class GrantController extends Base
{
	public function add_roleAction()
	{
		$grant = new GrantModel;
		$user = new UserModel;
		$role = new RoleModel;
		$user_id = trim($this->getPost('user_id',''));
		$role_id = trim($this->getPost('role_id',''));
		$data =  array();
		
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
		
		if(empty($role_id)){
			return $this->errorAjaxRender('角色ID不能为空');
		}
		$role_info = $role->roleInfo($role_id);
		if(empty($role_info)){
			return $this->errorAjaxRender('该角色不存在');
		}
		
		if(isset($role_info['is_delete']) && ($role_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该角色已经删除');
		}
		
		if(isset($role_info['status']) && ($role_info['status'] == 2)){
			return $this->errorAjaxRender('该角色状态为禁用');
		}
		
		$role_user_info = $grant->roleUserInfo($user_id,$role_id);
		if(!empty($role_user_info)){
			return $this->errorAjaxRender('已授权，不能再次授权');
		}
		
		$domain_id = $role_info['domain_id'];
		$data = array($user_id,$domain_id,$role_id);
		
		try{
			$ret = $grant->add_role($data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		if(!$ret){
			return $this->errorAjaxRender('授权失败');
		}else{
			return $this->ajaxRender(array(),'授权成功');
		}
	}
	
	public function del_roleAction()
	{
		$grant = new GrantModel;
		$user = new UserModel;
		$role = new RoleModel;
		$user_id = trim($this->getPost('user_id',1));
		$role_id = trim($this->getPost('role_id',1));
		$data =  array();
		
		if(empty($user_id)){
			return $this->errorAjaxRender('用户ID不能为空');	
		}
		
		if(empty($role_id)){
			return $this->errorAjaxRender('角色ID不能为空');
		}
		
		$role_user_info = $grant->roleUserInfo($user_id,$role_id);
		if(empty($role_user_info)){
			return $this->errorAjaxRender('该用户没有您要删除的角色');
		}
		
		try{
			$ret = $grant->del_role($user_id,$role_id);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		if(!$ret){
			return $this->errorAjaxRender('角色收回失败');
		}else{
			return $this->ajaxRender(array(),'角色收回成功');
		}
	}
	
	public function add_resourceAction()
	{
		$grant = new GrantModel;
		$role = new RoleModel;
		$resource = new ResourceModel;
		$role_id = trim($this->getPost('role_id',13));
		$resource_id = trim($this->getPost('resource_id',5));
		
		if(empty($role_id)){
			return $this->errorAjaxRender('角色ID不能为空');
		}
		$role_info = $role->roleInfo($role_id);
		if(empty($role_info)){
			return $this->errorAjaxRender('该角色不存在');
		}
		
		if(isset($role_info['is_delete']) && ($role_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该角色已经删除');
		}
		
		if(isset($role_info['status']) && ($role_info['status'] == 2)){
			return $this->errorAjaxRender('该角色状态为禁用');
		}
		
		if(empty($resource_id)){
			return $this->errorAjaxRender('权限ID不能为空');	
		}
		$resource_info = $resource->resourceInfo($resource_id);
		if(empty($resource_info)){
			return $this->errorAjaxRender('该权限不存在');
		}
		
		if(isset($resource_info['is_delete']) && ($resource_info['is_delete'] == 1)){
			return $this->errorAjaxRender('该权限已经删除');
		}
		
		if(isset($resource_info['status']) && ($resource_info['status'] == 2)){
			return $this->errorAjaxRender('该权限状态为禁用');
		}
		$role_resource_info = $grant->roleResourceInfo(/*$role_id,*/$resource_id);
	
		if(!empty($role_resource_info)){
			return $this->errorAjaxRender('该权限已有角色，同一权限不可以授予多个角色');
		}
		if($role_info['domain_id'] != $resource_info['domain_id']){
			return $this->errorAjaxRender('该角色和该权限不在同一产品线下，请确认再填写');
		}	
		
		$domain_id = $role_info['domain_id'];
		$resource_url = $resource_info['resource_url'];
		$data = array($domain_id,$role_id,$resource_id,$resource_url);
		
		try{
			$ret = $grant->add_resource($data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		if(!$ret){
			return $this->errorAjaxRender('添加权限失败');
		}else{
			return $this->ajaxRender(array(),'添加权限成功');
		}
		
	}
	
	public function del_resourceAction()
	{
		$grant = new GrantModel;
		$role_id = trim($this->getPost('role_id',''));
		$resource_id = trim($this->getPost('resource_id',''));
		if(empty($role_id)){
			return $this->errorAjaxRender('角色ID不能为空');
		}
		if(empty($resource_id)){
			return $this->errorAjaxRender('权限ID不能为空');	
		}
		$role_resource_info = $grant->roleResourceInfo($role_id,$resource_id);
		if(empty($role_resource_info)){
			return $this->errorAjaxRender('该角色没有您要删除的权限');
		}
		
		try{
			$ret = $grant->del_resource($role_id,$resource_id);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		if(!$ret){
			return $this->errorAjaxRender('权限删除失败');
		}else{
			return $this->ajaxRender(array(),'权限收回成功');
		}
	}
	
	
	
}