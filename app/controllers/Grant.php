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
			$error = '用户ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		$user_info = $user->userInfo($user_id);
		if(empty($user_info)){
			$error = '该用户不存在';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['is_delete']) && ($user_info['is_delete'] == 1)){
			$error = '该用户已经删除';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($user_info['status']) && ($user_info['status'] == 2)){
			$error = '该用户状态为禁用';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($role_id)){
			$error = '角色ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$role_info = $role->roleInfo($role_id);
		if(empty($role_info)){
			$error = '该角色不存在';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($role_info['is_delete']) && ($role_info['is_delete'] == 1)){
			$error = '该角色已经删除';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($role_info['status']) && ($role_info['status'] == 2)){
			$error = '该角色状态为禁用';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		$role_user_info = $grant->roleUserInfo($user_id,$role_id);
		if(!empty($role_user_info)){
			$error = '已授权，不能再次授权';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		$domain_id = $role_info['domain_id'];
		$data = array($user_id,$domain_id,$role_id);
		
		try{
			$ret = $grant->add_role($data);
		}catch(\Exception $e){
			$this->logger->debug($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		
		if(!$ret){
			$error = '授权失败';
			$this->logger->error($error,array('用户ID：'$user_id,'产品线ID：'.$domain_id,'角色ID：'.$role_id));
			return $this->errorAjaxRender($error);	
		}else{
			$info = '授权成功';
			$this->logger->info($info,array('用户ID：'$user_id,'产品线ID：'.$domain_id,'角色ID：'.$role_id));
			return $this->ajaxRender(array(),$info);
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
			$error = '用户ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		
		if(empty($role_id)){
			$error = '角色ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		$role_user_info = $grant->roleUserInfo($user_id,$role_id);
		if(empty($role_user_info)){
			$error = '该用户没有您要删除的角色';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		try{
			$ret = $grant->del_role($user_id,$role_id);
		}catch(\Exception $e){
			$this->logger->debug($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
			$info = '角色收回成功';
			$this->logger->debug($info,array('user_id:'.$user_id,'role_id'.$role_id));
			return $this->ajaxRender(array(),$info);
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
			$error = '角色ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$role_info = $role->roleInfo($role_id);
		if(empty($role_info)){
			$error = '该角色不存在';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($role_info['is_delete']) && ($role_info['is_delete'] == 1)){
			$error = '该角色已经删除';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($role_info['status']) && ($role_info['status'] == 2)){
			$error = '该角色状态为禁用';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($resource_id)){
			$error = '权限ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		$resource_info = $resource->resourceInfo($resource_id);
		if(empty($resource_info)){
			$error = '该权限不存在';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($resource_info['is_delete']) && ($resource_info['is_delete'] == 1)){
			$error = '该权限已经删除';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(isset($resource_info['status']) && ($resource_info['status'] == 2)){
			$error = '该权限状态为禁用';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$role_resource_info = $grant->roleResourceInfo($role_id,$resource_id);
		if(!empty($role_resource_info)){
			$error = '该权限已有角色，同一权限不可以授予多个角色';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if($role_info['domain_id'] != $resource_info['domain_id']){
			$error = '该角色和该权限不在同一产品线下';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}	
		
		$domain_id = $role_info['domain_id'];
		$resource_url = $resource_info['resource_url'];
		$data = array($domain_id,$role_id,$resource_id,$resource_url);
		
		try{
			$ret = $grant->add_resource($data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
			$this->logger->debug($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
		}
		$info = '添加权限成功';
		$this->logger->info($info,array('role_id'.$role_id,'resource_id'.$resource_id));
		return $this->ajaxRender(array(),$info);
		
		
	}
	
	public function del_resourceAction()
	{
		
		$grant = new GrantModel;
		$role_id = trim($this->getPost('role_id',''));
		$resource_id = trim($this->getPost('resource_id',''));
		if(empty($role_id)){
			$error = '角色ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(empty($resource_id)){
			$error = '权限ID不能为空';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);	
		}
		$role_resource_info = $grant->roleResourceInfo($role_id,$resource_id);
		if(empty($role_resource_info)){
			$error = '该角色没有您要删除的权限';
			$this->logger->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		try{
			$ret = $grant->del_resource($role_id,$resource_id);
		}catch(\Exception $e){
			$this->logger->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		$info = '权限收回成功';
		$this->logger->error($info,array('role_id'.$role_id,'resource_id'.$resource_id));
		return $this->ajaxRender(array(),$info);
		
	}
	
	
	
}